<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotImportException;
use NextDeveloper\IAAS\Jobs\VirtualMachines\FinalizeAgentImport;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * What ProvisioningCapableInterface asks of a driver, done through the xenserver HTTPS agent:
 * mount the image repository, import a VM from an image, name it, give it guest metadata, and make
 * its disks and network cards match the database config.
 *
 * Mirrors the SSH path (ComputeMemberXenService::mountVmRepository / importVirtualMachine,
 * XenServer82SshDriver::reconcileDiskConfiguration / reconcileNetworkConfiguration, and the
 * VirtualDiskImageXenService helpers) and writes the same rows. The SSH code is not touched.
 *
 * Differences from the SSH path, on purpose:
 *  - Disks and cards the config no longer wants are removed with the agent's own operations; the
 *    drafts are created with vdi.create / vbd.create / vif.create.
 *  - A card is attached here directly. The Attach action only uses the driver for drivers that
 *    implement NetworkCapableInterface and otherwise falls back to SSH, which this member cannot do.
 *  - A network that does not exist on the host is an error. The SSH path creates it on the fly.
 *  - Lazy deploy does not have the host call our API back. The import runs as an agent job and
 *    FinalizeAgentImport polls it (see that job).
 */
class VirtualMachineProvisioningAgentService
{
    /**
     * Where the repository is mounted on the host, same path as the SSH path.
     */
    public const REPOSITORY_MOUNT_ROOT = '/mnt/plusclouds-repo';

    public static function mountPoint(Repositories $repository): string
    {
        return self::REPOSITORY_MOUNT_ROOT . '/' . $repository->uuid;
    }

    // -- repository ----------------------------------------------------------------------------

    /**
     * Mounts the repository's VM export (NFS) on the host. Returns true when the repository's
     * hash.txt is visible afterwards, the same check as the SSH path.
     */
    public static function mountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);
        $mount  = self::mountPoint($repository);

        //  Idempotent on the agent: the same export at the same mount point is "already mounted".
        $client->call('nfsmount.mount', [
            'server'      => self::bareAddress($repository->local_ip_addr),
            'export_path' => $repository->vm_path,
            'mount_point' => $mount,
        ]);

        return self::fileNames($client, $mount)->contains('hash.txt');
    }

    /**
     * Unmounts the repository. Returns false when the mount is still busy, like the SSH path
     * returns false when the directory is not empty afterwards.
     */
    public static function unmountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        try {
            ComputeMemberAgentSyncService::client($computeMember)
                ->call('nfsmount.unmount', ['mount_point' => self::mountPoint($repository)]);
        } catch (XenAgentException $e) {
            //  Nothing mounted means nothing to unmount.
            if (str_contains($e->getMessage(), 'not mounted')) {
                return true;
            }

            Log::error('[VirtualMachineProvisioningAgentService@unmountRepository] The repository is not unmounted properly: ' .
                $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Mounts the ISO repository on the host as an ISO storage repository. No error if it is
     * already there (the agent checks the name first).
     */
    public static function mountIsoRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        ComputeMemberAgentSyncService::client($computeMember)->call('xenserver.sr.create', [
            'name_label'    => 'ISO on ' . $repository->name,
            'type'          => 'iso',
            'content_type'  => 'iso',
            'shared'        => true,
            'if_not_exists' => true,
            'device_config' => [
                'type'       => 'nfs_iso',
                'location'   => self::bareAddress($repository->local_ip_addr) . ':' . $repository->iso_path,
                'nfsversion' => '4',
            ],
        ]);

        return true;
    }

    // -- import ----------------------------------------------------------------------------------

    /**
     * Imports the VM from the repository image onto the volume's storage repository and names it
     * with our VM uuid.
     *
     * Not lazy: waits for the import (polling the agent job) and stores hypervisor_uuid.
     * Lazy: starts the import as an agent job, queues FinalizeAgentImport which picks the result
     * up later, and returns the job id. The caller (Commit) stops here and is resumed by that job,
     * exactly as the SSH path is resumed by the host's finalize-commit call.
     *
     * @return string the VM's hypervisor uuid (not lazy) or the agent job id (lazy)
     */
    public static function importFromImage(
        VirtualMachines $vm,
        ComputeMembers $computeMember,
        Repositories $repository,
        StorageVolumes $volume,
        RepositoryImages $image,
        bool $isLazyDeploy
    ): string {
        self::mountRepository($computeMember, $repository);

        $client = ComputeMemberAgentSyncService::client($computeMember);
        $mount  = self::mountPoint($repository);

        //  An image missing from the repository mount means our image list is out of date.
        if (!self::fileNames($client, $mount)->contains($image->filename)) {
            Events::fire('image-lost:NextDeveloper\Iaas\RepositoryImages', $image);

            throw new CannotImportException('I cannot find the given machine image in the repository. Seems like ' .
                'it is missing. I also fired an image-lost event, to syncronize the image service again.');
        }

        $mountedVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->where('iaas_storage_volume_id', $volume->id)
            ->first();

        if (!$mountedVolume) {
            throw new CannotImportException('The storage volume ' . $volume->uuid . ' is not mounted on compute member ' .
                $computeMember->name . ', so the VM cannot be imported there.');
        }

        $params = [
            'filename' => $mount . '/' . $image->filename,
            'sr_uuid'  => $mountedVolume->hypervisor_uuid,
            //  Name it with our uuid right away, so no second call (and no lookup by name) is needed.
            'new_name' => $vm->uuid,
        ];

        if ($isLazyDeploy) {
            $started = $client->call('xenserver.vm.import', $params + ['async' => true]);

            if (empty($started['job_id'])) {
                throw new CannotImportException('The agent did not start an import job for VM ' . $vm->uuid . '.');
            }

            dispatch(new FinalizeAgentImport($vm, $started['job_id']));

            return $started['job_id'];
        }

        $result = $client->callAsync('xenserver.vm.import', $params);
        $uuid   = $result['data']['uuid'] ?? null;

        if (!$uuid) {
            throw new CannotImportException('The import finished but the agent returned no VM uuid for VM ' . $vm->uuid . '.');
        }

        $vm->update(['hypervisor_uuid' => $uuid]);

        return $uuid;
    }

    // -- the VM on the host ---------------------------------------------------------------------

    public static function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        return ComputeMemberAgentSyncService::flatten(
            ComputeMemberAgentSyncService::client($computeMember)->call('xenserver.vm.param_list', ['uuid' => $ref])
        );
    }

    /**
     * Sets the host-side name of the VM to our uuid and checks it took.
     */
    public static function renameVirtualMachine(VirtualMachines $vm): bool
    {
        $computeMember = self::computeMember($vm);
        $client        = ComputeMemberAgentSyncService::client($computeMember);

        $client->call('xenserver.vm.param_set', [
            'uuid' => $vm->hypervisor_uuid,
            'set'  => ['name-label' => $vm->uuid],
        ]);

        $params = ComputeMemberAgentSyncService::flatten(
            $client->call('xenserver.vm.param_list', ['uuid' => $vm->hypervisor_uuid])
        );

        return ($params['name-label'] ?? null) === $vm->uuid;
    }

    /**
     * Writes one xenstore-data key. The agent allows keys of letters, digits and _ . - only.
     */
    public static function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        ComputeMemberAgentSyncService::client(self::computeMember($vm))->call('xenserver.vm.param_set', [
            'uuid' => $vm->hypervisor_uuid,
            'set'  => ['xenstore-data:' . $key => $value],
        ]);

        return true;
    }

    // -- disks -----------------------------------------------------------------------------------

    /**
     * Makes the VM's disks on the host match the database config: disks that match a config row by
     * device number are synced (and grown if the config asks for more), disks and empty CD drives
     * with no config row are removed, and draft disks are created and attached.
     */
    public static function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        $computeMember = self::computeMember($vm);
        $client        = ComputeMemberAgentSyncService::client($computeMember);

        self::renameVirtualMachine($vm);

        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        $vmParams = ComputeMemberAgentSyncService::flatten(
            $client->call('xenserver.vm.param_list', ['uuid' => $vm->hypervisor_uuid])
        );

        $vbds = [];

        foreach (($vmParams['VBDs'] ?? []) as $vbdUuid) {
            $vbds[] = ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.vbd.param_list', ['vbd_uuid' => $vbdUuid])
            );
        }

        $syncedVbds = [];

        foreach ($vbds as $vbd) {
            foreach ($diskConfig as $config) {
                //  Same slot on the host and in the config: this disk is the configured one.
                if ($vbd['userdevice'] == $config->device_number) {
                    self::syncDiskConfig($vm, $computeMember, $config, $vbd);
                    $syncedVbds[] = $vbd['uuid'];
                }
            }
        }

        //  What the imported image brought that the config does not list is removed. The VBD goes
        //  first: the agent refuses to destroy a disk that is still attached.
        foreach ($vbds as $vbd) {
            if (in_array($vbd['uuid'], $syncedVbds, true)) {
                continue;
            }

            $client->call('xenserver.vbd.destroy', ['vbd_uuid' => $vbd['uuid']]);

            if (($vbd['vdi-uuid'] ?? '<not in database>') !== '<not in database>') {
                $client->call('xenserver.vdi.destroy', ['vdi_uuid' => $vbd['vdi-uuid'], 'confirm' => true]);
            }
        }

        //  Draft disks are created now.
        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        foreach ($diskConfig as $disk) {
            if (!$disk->is_draft) {
                continue;
            }

            $disk = self::createDisk($disk, $computeMember, $vm);
            $disk = self::attachDisk($disk, $computeMember, $vm);

            //  Normal update (observers fire); admin because a queued action has no signed in user.
            UserHelper::runAsAdmin(function () use ($disk) {
                $disk->update(['is_draft' => false]);
            });
        }
    }

    private static function syncDiskConfig(VirtualMachines $vm, ComputeMembers $computeMember, VirtualDiskImages $config, array $vbd): void
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);
        $isCdrom = ($vbd['type'] ?? null) === 'CD';

        $diskParams = [];

        if (!$isCdrom) {
            $diskParams = ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.vdi.param_list', ['vdi_uuid' => $vbd['vdi-uuid']])
            );

            //  The agent (like xapi) only grows a disk. A config size that is not bigger is left alone.
            if ((int) $config->size > (int) $diskParams['virtual-size']) {
                $client->call('xenserver.vdi.resize', ['vdi_uuid' => $vbd['vdi-uuid'], 'size_bytes' => (int) $config->size]);

                $diskParams = ComputeMemberAgentSyncService::flatten(
                    $client->call('xenserver.vdi.param_list', ['vdi_uuid' => $vbd['vdi-uuid']])
                );
            }

            if ((int) $config->size != (int) $diskParams['virtual-size']) {
                StateHelper::setState($config, 'disk_cannot_resized', 'Disk cannot resized. Current size is: ' . $diskParams['virtual-size'], 'warn');
            }
        }

        $diskVolume = $isCdrom ? null : ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $diskParams['sr-uuid'])
            ->first();

        UserHelper::runAsAdmin(function () use ($config, $vm, $vbd, $diskParams, $isCdrom, $diskVolume) {
            $config->update([
                'name'                      => !$isCdrom ? $config->name : 'CDROM',
                'size'                      => !$isCdrom ? $diskParams['virtual-size'] : 0,
                'physical_utilisation'      => !$isCdrom ? $diskParams['physical-utilisation'] : 0,
                'iaas_storage_volume_id'    => $isCdrom ? null : $diskVolume?->iaas_storage_volume_id,
                'iaas_virtual_machine_id'   => $vm->id,
                'device_number'             => $vbd['userdevice'],
                'is_cdrom'                  => $isCdrom,
                'hypervisor_uuid'           => $vbd['vdi-uuid'],
                'hypervisor_data'           => $vbd,
                'vbd_hypervisor_uuid'       => $vbd['uuid'],
                'vbd_hypervisor_data'       => $vbd,
                'iam_account_id'            => $vm->iam_account_id,
                'iam_user_id'               => $vm->iam_user_id,
                'is_draft'                  => false,
            ]);
        });
    }

    /**
     * Creates the disk (VDI) of a draft config row on the storage repository of its storage pool.
     */
    public static function createDisk(VirtualDiskImages $vdi, ComputeMembers $computeMember, VirtualMachines $vm): VirtualDiskImages
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        $cmVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->where('iaas_storage_pool_id', $vdi->iaas_storage_pool_id)
            ->first();

        if (!$cmVolume) {
            throw new XenAgentException('Compute member ' . $computeMember->name . ' has no storage volume in storage pool ' .
                $vdi->iaas_storage_pool_id . ', so disk ' . $vdi->uuid . ' cannot be created.');
        }

        $result = $client->call('xenserver.vdi.create', [
            'sr_uuid'   => $cmVolume->hypervisor_uuid,
            'new_name'  => $vdi->uuid,
            'size_bytes' => (int) $vdi->size,
            'type'      => 'user',
        ]);

        $vdiUuid = $result['data']['uuid'] ?? null;

        if (!$vdiUuid) {
            throw new XenAgentException('The agent created disk ' . $vdi->uuid . ' but returned no uuid.');
        }

        $vdi->updateQuietly([
            'hypervisor_uuid' => $vdiUuid,
            'hypervisor_data' => ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.vdi.param_list', ['vdi_uuid' => $vdiUuid])
            ),
        ]);

        return $vdi->fresh();
    }

    /**
     * Attaches the disk to the VM (creates the VBD, plugs it if the VM runs).
     */
    public static function attachDisk(VirtualDiskImages $vdi, ComputeMembers $computeMember, VirtualMachines $vm): VirtualDiskImages
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        if (!$vdi->vbd_hypervisor_uuid) {
            $result = $client->call('xenserver.vbd.create', [
                'uuid'     => $vm->hypervisor_uuid,
                'vdi_uuid' => $vdi->hypervisor_uuid,
                'device'   => (string) $vdi->device_number,
                'mode'     => 'RW',
                'type'     => 'Disk',
            ]);

            $vdi->updateQuietly(['vbd_hypervisor_uuid' => $result['data']['uuid'] ?? null]);
            $vdi = $vdi->fresh();
        }

        if ($vm->status == 'running') {
            try {
                $client->call('xenserver.vbd.plug', ['vbd_uuid' => $vdi->vbd_hypervisor_uuid]);
            } catch (XenAgentException $e) {
                //  Hot-plug needs the guest tools. Without them the disk is defined on the VM (it appears at
                //  the next boot) but not plugged; that is not a failed attach, same as for network cards.
                $message = strtolower($e->getMessage());

                if (!str_contains($message, 'guest tools') && !str_contains($message, 'pv drivers')) {
                    throw $e;
                }

                Log::warning('[VirtualMachineProvisioningAgentService@attachDisk] Disk ' . $vdi->uuid .
                    ' is defined but could not be hot-plugged: ' . $e->getMessage());
            }
        }

        $vdi->updateQuietly([
            'vbd_hypervisor_data' => ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.vbd.param_list', ['vbd_uuid' => $vdi->vbd_hypervisor_uuid])
            ),
        ]);

        return $vdi->fresh();
    }

    /**
     * Takes the disk off the VM: unplugs it if the VM runs, then destroys the VBD. The VBD may
     * already be gone (destroying the VM removes it), which is not an error here.
     */
    public static function detachDisk(VirtualDiskImages $vdi, ComputeMembers $computeMember, VirtualMachines $vm): VirtualDiskImages
    {
        if (!$vdi->vbd_hypervisor_uuid) {
            return $vdi;
        }

        $client = ComputeMemberAgentSyncService::client($computeMember);

        //  Unplug and destroy are separate steps: a failed unplug ("already unplugged", or a guest without
        //  tools) must not skip the destroy, or the disk stays attached and cannot be destroyed later.
        if ($vm->status == 'running') {
            try {
                $client->call('xenserver.vbd.unplug', ['vbd_uuid' => $vdi->vbd_hypervisor_uuid]);
            } catch (XenAgentException $e) {
                Log::warning('[VirtualMachineProvisioningAgentService@detachDisk] VBD ' . $vdi->vbd_hypervisor_uuid .
                    ' was not unplugged, destroying it anyway: ' . $e->getMessage());
            }
        }

        try {
            $client->call('xenserver.vbd.destroy', ['vbd_uuid' => $vdi->vbd_hypervisor_uuid]);
        } catch (XenAgentException $e) {
            //  Gone already (destroying the VM removes its VBDs) is fine; anything else is a real failure
            //  and the row keeps its vbd uuid, so the detach can be tried again.
            $message = strtolower($e->getMessage());

            if (!str_contains($message, 'does not exist') && !str_contains($message, 'not found')) {
                throw $e;
            }

            Log::warning('[VirtualMachineProvisioningAgentService@detachDisk] VBD ' . $vdi->vbd_hypervisor_uuid .
                ' was already gone from the host: ' . $e->getMessage());
        }

        $vdi->updateQuietly(['vbd_hypervisor_uuid' => null, 'vbd_hypervisor_data' => null]);

        return $vdi->fresh();
    }

    /**
     * Destroys the disk on the host (after detaching it). The agent refuses to destroy a disk that
     * is still attached to a VM, so a failed detach shows up here as an error, never as a silent
     * success that leaves the disk behind.
     */
    public static function destroyDisk(VirtualDiskImages $vdi, ComputeMembers $computeMember, VirtualMachines $vm): bool
    {
        $vdi = self::detachDisk($vdi, $computeMember, $vm);

        if ($vdi->hypervisor_uuid) {
            ComputeMemberAgentSyncService::client($computeMember)
                ->call('xenserver.vdi.destroy', ['vdi_uuid' => $vdi->hypervisor_uuid, 'confirm' => true]);
        }

        return true;
    }

    /**
     * Grows the disk (the agent, like xapi, never shrinks one).
     */
    public static function resizeDisk(VirtualDiskImages $vdi, ComputeMembers $computeMember, int $sizeInBytes): VirtualDiskImages
    {
        ComputeMemberAgentSyncService::client($computeMember)
            ->call('xenserver.vdi.resize', ['vdi_uuid' => $vdi->hypervisor_uuid, 'size_bytes' => $sizeInBytes]);

        $vdi->update(['size' => $sizeInBytes]);

        return $vdi->fresh();
    }

    /**
     * Refreshes the disk record from the host: size, utilisation, storage volume and its VBD.
     */
    public static function syncDiskFromHypervisor(VirtualDiskImages $vdi, ComputeMembers $computeMember): VirtualDiskImages
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        $diskParams = ComputeMemberAgentSyncService::flatten(
            $client->call('xenserver.vdi.param_list', ['vdi_uuid' => $vdi->hypervisor_uuid])
        );

        $vbdParams = $vdi->vbd_hypervisor_uuid
            ? ComputeMemberAgentSyncService::flatten($client->call('xenserver.vbd.param_list', ['vbd_uuid' => $vdi->vbd_hypervisor_uuid]))
            : null;

        $volume = \NextDeveloper\IAAS\Services\StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);

        if (!$volume) {
            //  The SR is new to us, run the storage sync once and look again.
            ComputeMemberAgentSyncService::updateStorageVolumes($computeMember);
            $volume = \NextDeveloper\IAAS\Services\StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);
        }

        $vdi->updateQuietly(array_filter([
            'size'                   => $diskParams['virtual-size'],
            'physical_utilisation'   => $diskParams['physical-utilisation'],
            'hypervisor_data'        => $diskParams,
            'is_draft'               => false,
            'vbd_hypervisor_data'    => $vbdParams,
            'iaas_storage_volume_id' => $volume?->id,
            'iaas_storage_pool_id'   => $volume?->iaas_storage_pool_id,
        ], fn($value) => $value !== null));

        return $vdi->fresh();
    }

    // -- network cards ----------------------------------------------------------------------------

    /**
     * Makes the VM's network cards on the host match the database config: cards that match a config
     * row by device number are synced, cards with no config row are removed, and config rows with
     * no card on the host get one created.
     */
    public static function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
        $computeMember = self::computeMember($vm);
        $client        = ComputeMemberAgentSyncService::client($computeMember);

        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        $vifs = $client->call('xenserver.vif.list', ['uuid' => $vm->hypervisor_uuid]);

        $syncedVifs = [];

        foreach ($vifs as $vif) {
            foreach ($netConfig as $config) {
                if ($config->device_number == $vif['device']) {
                    self::syncVifConfig($computeMember, $vif['uuid'], $config);
                    $syncedVifs[] = $vif['uuid'];
                }
            }
        }

        foreach ($vifs as $vif) {
            if (!in_array($vif['uuid'], $syncedVifs, true)) {
                $client->call('xenserver.vif.remove', ['vif_uuid' => $vif['uuid']]);
            }
        }

        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        foreach ($netConfig as $config) {
            //  No hypervisor_uuid means the card does not exist on the host yet.
            if ($config->hypervisor_uuid == null) {
                self::attachNetworkCard($vm, $computeMember, $config);
            }
        }
    }

    private static function syncVifConfig(ComputeMembers $computeMember, string $vifUuid, VirtualNetworkCards $config): void
    {
        $vif = ComputeMemberAgentSyncService::flatten(
            ComputeMemberAgentSyncService::client($computeMember)->call('xenserver.vif.param_list', ['vif_uuid' => $vifUuid])
        );

        $interface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->where('network_uuid', $vif['network-uuid'])
            ->first();

        $network = $interface
            ? Networks::withoutGlobalScope(AuthorizationScope::class)->where('vlan', $interface->vlan)->first()
            : null;

        UserHelper::runAsAdmin(function () use ($config, $vif, $network) {
            $config->update([
                'hypervisor_uuid'   => $vif['uuid'],
                'hypervisor_data'   => $vif,
                'mac_addr'          => $vif['MAC'],
                'iaas_network_id'   => $network ? $network->id : null,
                'is_draft'          => false,
            ]);
        });
    }

    /**
     * Creates the card on the host. The host network is the one of this member's interface for the
     * card's network (same VLAN). The SSH path creates the network if the host lacks it; here a
     * missing network is an error, creating networks on a host is not part of a VM deploy.
     */
    private static function attachNetworkCard(VirtualMachines $vm, ComputeMembers $computeMember, VirtualNetworkCards $config): void
    {
        $client  = ComputeMemberAgentSyncService::client($computeMember);
        $network = Networks::withoutGlobalScope(AuthorizationScope::class)->where('id', $config->iaas_network_id)->first();

        $interface = $network
            ? ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $computeMember->id)
                ->where('vlan', $network->vlan)
                ->orderByDesc('is_management')
                ->first()
            : null;

        if (!$interface) {
            throw new XenAgentException('Network ' . ($network?->uuid ?? $config->iaas_network_id) . ' (vlan ' .
                ($network?->vlan ?? '?') . ') does not exist on compute member ' . $computeMember->name . '.');
        }

        $result = $client->call('xenserver.vif.create', [
            'uuid'         => $vm->hypervisor_uuid,
            'device'       => (string) $config->device_number,
            'network_uuid' => $interface->network_uuid,
        ]);

        $vifUuid = $result['data']['uuid'] ?? null;

        if (!$vifUuid) {
            throw new XenAgentException('The agent created the network card of VM ' . $vm->uuid . ' but returned no uuid.');
        }

        self::syncVifConfig($computeMember, $vifUuid, $config);

        $config->updateQuietly(['status' => 'attached:true']);
    }

    /**
     * Creates the host side of an existing draft card row (device number and network already set) and
     * updates the row in place. A running VM gets the card hot-plugged; that needs the guest tools,
     * so a failed plug is logged and the card stays defined (currently-attached=false) instead of
     * rolling back a card that exists on the host.
     */
    public static function attachDraftNetworkCard(VirtualNetworkCards $vif): VirtualNetworkCards
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('id', $vif->iaas_virtual_machine_id)->firstOrFail();

        $computeMember = self::computeMember($vm);

        self::attachNetworkCard($vm, $computeMember, $vif);

        $vif = $vif->fresh();

        $power = ComputeMemberAgentSyncService::flatten(
            ComputeMemberAgentSyncService::client($computeMember)->call('xenserver.vm.param_list', ['uuid' => $vm->hypervisor_uuid])
        )['power-state'] ?? null;

        if ($power === 'running') {
            try {
                ComputeMemberAgentSyncService::client($computeMember)->call('xenserver.vif.plug', ['vif_uuid' => $vif->hypervisor_uuid]);
                self::syncVifConfig($computeMember, $vif->hypervisor_uuid, $vif);
            } catch (XenAgentException $e) {
                Log::warning('[VirtualMachineProvisioningAgentService@attachDraftNetworkCard] Card ' . $vif->uuid .
                    ' is defined but could not be hot-plugged: ' . $e->getMessage());
            }
        }

        return $vif->fresh();
    }

    /**
     * Removes a card from the host. A card that is plugged into a running VM is unplugged first.
     * A card that is already gone from the host is not an error (same as a repeated detach).
     */
    public static function destroyNetworkCard(VirtualNetworkCards $vif): bool
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('id', $vif->iaas_virtual_machine_id)->first();

        if (!$vm || !$vif->hypervisor_uuid) {
            return false;
        }

        $client = ComputeMemberAgentSyncService::client(self::computeMember($vm));

        try {
            $currentlyAttached = ($vif->hypervisor_data['currently-attached'] ?? 'false') === 'true';

            if ($currentlyAttached) {
                $client->call('xenserver.vif.unplug', ['vif_uuid' => $vif->hypervisor_uuid]);
            }
        } catch (XenAgentException $e) {
            Log::warning('[VirtualMachineProvisioningAgentService@destroyNetworkCard] Unplug of ' . $vif->hypervisor_uuid . ' failed: ' . $e->getMessage());
        }

        try {
            $client->call('xenserver.vif.remove', ['vif_uuid' => $vif->hypervisor_uuid]);
        } catch (XenAgentException $e) {
            if (!str_contains(strtolower($e->getMessage()), 'does not exist') && !str_contains(strtolower($e->getMessage()), 'not found')) {
                throw $e;
            }

            Log::warning('[VirtualMachineProvisioningAgentService@destroyNetworkCard] Card ' . $vif->hypervisor_uuid . ' was already gone from the host.');
        }

        return true;
    }

    /**
     * Re-reads the card from the host into its row.
     */
    public static function syncNetworkCard(VirtualNetworkCards $vif): VirtualNetworkCards
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('id', $vif->iaas_virtual_machine_id)->firstOrFail();

        if ($vif->hypervisor_uuid) {
            self::syncVifConfig(self::computeMember($vm), $vif->hypervisor_uuid, $vif);
        }

        return $vif->fresh();
    }

    /**
     * Writes the card's IP allow-list (the IpAddresses of the card) and the locking mode in one call.
     * The agent has one operation (vif.set_acl) for both, so the mode is taken from the card as it is.
     */
    public static function setVifAcl(VirtualNetworkCards $vif, string $lockingMode, ?array $ipv4Allowed = null): bool
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('id', $vif->iaas_virtual_machine_id)->firstOrFail();

        $params = ['vif_uuid' => $vif->hypervisor_uuid, 'locking_mode' => $lockingMode];

        if ($ipv4Allowed !== null) {
            $params['ipv4_allowed'] = $ipv4Allowed;
        }

        ComputeMemberAgentSyncService::client(self::computeMember($vm))->call('xenserver.vif.set_acl', $params);

        self::syncVifConfig(self::computeMember($vm), $vif->hypervisor_uuid, $vif);

        return true;
    }

    /**
     * Sets allow-listed VIF parameters with param_set (agent key "set") and re-reads the card, so the
     * row always shows what the host really has afterwards.
     */
    public static function setVifParams(VirtualNetworkCards $vif, array $set): bool
    {
        $vm            = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('id', $vif->iaas_virtual_machine_id)->firstOrFail();
        $computeMember = self::computeMember($vm);

        ComputeMemberAgentSyncService::client($computeMember)->call('xenserver.vif.param_set', [
            'vif_uuid' => $vif->hypervisor_uuid,
            'set'      => $set,
        ]);

        self::syncVifConfig($computeMember, $vif->hypervisor_uuid, $vif);

        return true;
    }

    // -- helpers -------------------------------------------------------------------------------------

    private static function computeMember(VirtualMachines $vm): ComputeMembers
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        if (!$computeMember) {
            throw new XenAgentException('VM ' . $vm->uuid . ' has no compute member.');
        }

        return $computeMember;
    }

    /**
     * File names directly under a mounted repository path.
     */
    private static function fileNames(XenAgentClient $client, string $mountPoint): \Illuminate\Support\Collection
    {
        return collect($client->call('nfsmount.list_files', ['mount_point' => $mountPoint]))->pluck('name');
    }

    /**
     * Repository addresses are inet values and may carry a mask (10.0.0.5/24).
     */
    private static function bareAddress($address): string
    {
        return trim(explode('/', (string) $address)[0]);
    }
}
