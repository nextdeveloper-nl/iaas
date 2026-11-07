<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * VM-level sync for compute members managed over the HTTPS agent: the VirtualMachines rows, and
 * their VirtualDiskImages and VirtualNetworkCards, plus the per-volume disk sync.
 *
 * Mirrors XenServer82SshDriver::syncVirtualMachines / syncVirtualMachineRecord /
 * syncVirtualMachineDisks / syncVirtualMachineNetworkCards / syncStorageVolumeDisks and writes the
 * same rows, reading from the agent instead of `xe` over SSH. The SSH driver is not touched.
 * Host-level sync lives in ComputeMemberAgentSyncService.
 *
 * Differences from the SSH path, on purpose:
 *  - The agent's guest-VM list leaves out control domains, templates and snapshots. `xe vm-list`
 *    also returns dom0, so the SSH path creates a VM row for the host's control domain; this one
 *    does not.
 *  - Failures in one VM's disks or cards are logged and skipped, like the SSH path does for VIFs,
 *    so one bad record does not stop the host.
 *  - Guards where the SSH code would dereference null (a disk whose storage volume row is missing,
 *    a VIF with no existing row).
 */
class VirtualMachineAgentSyncService
{
    public static function syncVirtualMachines(ComputeMembers $computeMember): ComputeMembers
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        $snapshot = $client->call('xenserver.inventory.snapshot', ['include_vms' => true]);
        $vms      = array_map([ComputeMemberAgentSyncService::class, 'flatten'], $snapshot['vms'] ?? []);

        Log::info('[VirtualMachineAgentSyncService@syncVirtualMachines] Found ' . count($vms) .
            ' virtual machines on compute member: ' . $computeMember->name);

        foreach ($vms as $vmInfo) {
            //  An entry without a uuid would turn where('hypervisor_uuid', null) into whereNull()
            //  and overwrite an unrelated draft VM, so it is skipped outright.
            if (empty($vmInfo['uuid'])) {
                Log::warning('[VirtualMachineAgentSyncService@syncVirtualMachines] Skipping a VM entry with no uuid from compute member: ' . $computeMember->name);
                continue;
            }

            //  Same rule as the SSH path: exported copies are not real servers.
            if (Str::startsWith($vmInfo['name-label'] ?? '', 'exported_')) {
                continue;
            }

            $dbVm = self::syncVirtualMachineRecord($computeMember, $vmInfo);

            if (!$dbVm) {
                continue;
            }

            self::syncVirtualMachineDisks($computeMember, $dbVm, $vmInfo);
            self::syncVirtualMachineNetworkCards($computeMember, $dbVm, $vmInfo);
        }

        return $computeMember->fresh();
    }

    /**
     * Creates the VirtualMachines row for a hypervisor VM we have not seen, updates it otherwise.
     */
    private static function syncVirtualMachineRecord(ComputeMembers $computeMember, array $vmInfo): ?VirtualMachines
    {
        $dbVm = VirtualMachines::withoutGlobalScopes()->where('hypervisor_uuid', $vmInfo['uuid'])->first();

        //  The uuid comes from the host. An existing row may only be adopted inside the member's
        //  own pool (a VM that moved between members of one pool keeps its uuid). A row that belongs
        //  to another pool is never overwritten or re-homed by a host's report.
        if ($dbVm && !self::inPoolOf($computeMember, $dbVm->iaas_compute_pool_id)) {
            Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineRecord] Compute member ' . $computeMember->name .
                ' reported VM uuid ' . $vmInfo['uuid'] . ' that belongs to another compute pool. Skipped, needs review.');

            return null;
        }

        $computePool = ComputePools::withoutGlobalScopes()->where('id', $computeMember->iaas_compute_pool_id)->first();
        $cloudNode   = CloudNodes::withoutGlobalScopes()->where('id', $computePool->iaas_cloud_node_id)->first();

        $data = [
            'domain_type'           =>  ($vmInfo['hvm'] ?? 'false') == 'false' ? 'pv' : 'hvm',
            'cpu'                   =>  $vmInfo['VCPUs-max'],
            //  memory-static-max is in bytes, the column is MB
            'ram'                   =>  $vmInfo['memory-static-max'] / 1024 / 1024,
            'status'                =>  $vmInfo['power-state'],
            'current_operations'    =>  $vmInfo['current-operations'] ?? [],
            'blocked_operations'    =>  $vmInfo['blocked-operations'] ?? [],
            'hypervisor_uuid'       =>  $vmInfo['uuid'],
            'hypervisor_data'       =>  $vmInfo,
            'is_draft'              =>  false,
            'iaas_compute_member_id'    =>  $computeMember->id,
            'iaas_cloud_node_id'        =>  $cloudNode->id,
            'iaas_compute_pool_id'      =>  $computePool->id,
        ];

        if ($dbVm) {
            //  Merge so that other keys (e.g. 'agent') set by other sources are not overwritten.
            $data['available_operations'] = array_merge(
                $dbVm->available_operations ?? [],
                ['hypervisor' => $vmInfo['allowed-operations'] ?? []]
            );

            $dbVm->updateQuietly($data);

            return $dbVm->fresh();
        }

        return VirtualMachines::create($data + [
            'name'                  =>  $vmInfo['name-label'],
            'available_operations'  =>  ['hypervisor' => $vmInfo['allowed-operations'] ?? []],
            'iam_account_id'        =>  $computeMember->iam_account_id,
            'iam_user_id'           =>  $computeMember->iam_user_id,
        ]);
    }

    // -- disks ---------------------------------------------------------------------------

    /**
     * Upserts the VirtualDiskImages rows for the VM's VBDs/VDIs, including the CDROM slot.
     */
    private static function syncVirtualMachineDisks(ComputeMembers $computeMember, VirtualMachines $dbVm, array $vmInfo): void
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        foreach (($vmInfo['VBDs'] ?? []) as $vbdUuid) {
            try {
                $vbdParams = ComputeMemberAgentSyncService::flatten(
                    $client->call('xenserver.vbd.param_list', ['vbd_uuid' => $vbdUuid])
                );

                $isCdrom = ($vbdParams['type'] ?? null) === 'CD';

                $diskParams = [];

                if (!$isCdrom) {
                    $diskParams = ComputeMemberAgentSyncService::flatten(
                        $client->call('xenserver.vdi.param_list', ['vdi_uuid' => $vbdParams['vdi-uuid']])
                    );
                }
            } catch (XenAgentException $e) {
                Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineDisks] Cannot read VBD ' . $vbdUuid .
                    ' of VM ' . $dbVm->uuid, $e->getSafeContext());
                continue;
            }

            if ($isCdrom) {
                $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('is_cdrom', true)
                    ->where('iaas_virtual_machine_id', $dbVm->id)
                    ->first();
            } else {
                $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $diskParams['uuid'])
                    ->first();

                if ($dbVdi && !self::vmRowInPoolOf($computeMember, $dbVdi->iaas_virtual_machine_id)) {
                    Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineDisks] Disk ' . $diskParams['uuid'] .
                        ' is recorded on a VM of another compute pool. Skipped, needs review.');
                    continue;
                }

                //  Found by hypervisor_uuid but owned by another VM: prefer this VM's own record
                //  for the device slot (e.g. made by a migration clone) to avoid breaking the
                //  (iaas_virtual_machine_id, device_number) unique index.
                if ($dbVdi && (int) $dbVdi->iaas_virtual_machine_id !== (int) $dbVm->id) {
                    $ownRecord = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                        ->where('iaas_virtual_machine_id', $dbVm->id)
                        ->where('device_number', $vbdParams['userdevice'])
                        ->first();

                    if ($ownRecord) {
                        $dbVdi = $ownRecord;
                    } else {
                        Log::warning('[VirtualMachineAgentSyncService@syncVirtualMachineDisks] Disk ' . $diskParams['uuid'] .
                            ' is recorded on another VM and will be reassigned to VM ' . $dbVm->uuid);
                    }
                }
            }

            $storageVolumeId = null;

            if (!$isCdrom) {
                $diskVolume = self::findComputeMemberVolume($computeMember, $diskParams['sr-uuid']);

                if (!$diskVolume) {
                    Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineDisks] No storage volume row for SR ' .
                        $diskParams['sr-uuid'] . ', skipping disk ' . $diskParams['uuid'] . '. Run the storage volume sync.');
                    continue;
                }

                $storageVolumeId = $diskVolume->iaas_storage_volume_id;
            }

            $data = [
                'name'                      =>  !$isCdrom ? 'Disk of: ' . $dbVm->name : 'CDROM',
                'size'                      =>  !$isCdrom ? $diskParams['virtual-size'] : 0,
                'physical_utilisation'      =>  !$isCdrom ? $diskParams['physical-utilisation'] : 0,
                'iaas_storage_volume_id'    =>  $storageVolumeId,
                'iaas_virtual_machine_id'   =>  $dbVm->id,
                'device_number'             =>  $vbdParams['userdevice'],
                'is_cdrom'                  =>  $isCdrom,
                'hypervisor_uuid'           =>  $vbdParams['vdi-uuid'],
                'hypervisor_data'           =>  $diskParams,
                'iam_account_id'            =>  $dbVm->iam_account_id,
                'iam_user_id'               =>  $dbVm->iam_user_id,
                'is_draft'                  =>  false,
                'vbd_hypervisor_uuid'       =>  $vbdUuid,
                'vbd_hypervisor_data'       =>  $vbdParams
            ];

            if ($dbVdi) {
                $dbVdi->updateQuietly($data);
                continue;
            }

            //  A record for this (vm, device slot) may already exist, even soft-deleted, e.g.
            //  after the VDI moved to another storage. Reuse it instead of colliding with the index.
            $checkVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $dbVm->id)
                ->where('device_number', $vbdParams['userdevice'])
                ->withTrashed()
                ->first();

            if ($checkVdi) {
                if ($checkVdi->trashed()) {
                    $checkVdi->restore();
                }

                $checkVdi->updateQuietly($data);
            } else {
                VirtualDiskImages::create($data);
            }
        }
    }

    // -- network cards -----------------------------------------------------------------------

    /**
     * Upserts the VirtualNetworkCards rows for the VM's VIFs.
     */
    private static function syncVirtualMachineNetworkCards(ComputeMembers $computeMember, VirtualMachines $dbVm, array $vmInfo): void
    {
        $client      = ComputeMemberAgentSyncService::client($computeMember);
        $computePool = ComputePools::withoutGlobalScopes()->where('id', $computeMember->iaas_compute_pool_id)->first();

        //  The card uuids come from vif.list, not from the VM's parameters: the agent's
        //  vm.param_list does not return the VIFs set (checked on a real host, VBDs is there but
        //  VIFs is not), so reading it would silently sync no cards at all.
        $vifUuids = array_column($client->call('xenserver.vif.list', ['uuid' => $dbVm->hypervisor_uuid]), 'uuid');

        foreach ($vifUuids as $vifUuid) {
            try {
                $vifParams = ComputeMemberAgentSyncService::flatten(
                    $client->call('xenserver.vif.param_list', ['vif_uuid' => $vifUuid])
                );
            } catch (XenAgentException $e) {
                Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineNetworkCards] Cannot read VIF ' . $vifUuid .
                    ' of VM ' . $dbVm->uuid, $e->getSafeContext());
                continue;
            }

            $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $vifUuid)
                ->first();

            if ($dbVif && !self::vmRowInPoolOf($computeMember, $dbVif->iaas_virtual_machine_id)) {
                Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineNetworkCards] VIF ' . $vifUuid .
                    ' is recorded on a VM of another compute pool. Skipped, needs review.');
                continue;
            }

            if (!$dbVif) {
                $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_virtual_machine_id', $dbVm->id)
                    ->where('mac_addr', $vifParams['MAC'])
                    ->withTrashed()
                    ->first();

                if ($dbVif && $dbVif->trashed()) {
                    $dbVif->restore();
                }
            }

            //  Scoped to this member: network uuids are pool-wide, the interface rows are per host.
            $connectedInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $computeMember->id)
                ->where('network_uuid', $vifParams['network-uuid'])
                ->first();

            if (!$connectedInterface) {
                //  Ask for a host scan, the interface rows are probably out of date.
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineNetworkCards] Cannot find the connected ' .
                    'interface for the VIF: ' . $vifUuid . '. This compute member should be scanned and synced.');
                continue;
            }

            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $connectedInterface->vlan)
                ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                ->first();

            if (!$network) {
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineNetworkCards] Cannot find the network for ' .
                    'the VIF: ' . $vifUuid . ' (vlan ' . $connectedInterface->vlan . '). This compute member should be scanned and synced.');
                continue;
            }

            $data = [
                'name'                      =>  'eth' . $vifParams['device'],
                'device_number'             =>  $vifParams['device'],
                'mac_addr'                  =>  $vifParams['MAC'],
                'bandwidth_limit'           =>  '-1',
                'iaas_network_id'           =>  $network->id,
                'hypervisor_uuid'           =>  $vifUuid,
                'hypervisor_data'           =>  $vifParams,
                'iam_account_id'            =>  $dbVm->iam_account_id,
                'iam_user_id'               =>  $dbVm->iam_user_id,
                'is_draft'                  =>  false,
                'status'                    =>  'attached:true',
                'iaas_virtual_machine_id'   =>  $dbVm->id
            ];

            //  Another card on the same device slot or MAC is a stale record: remove it, unless it
            //  is the one being updated. (The SSH code dereferences $dbVif here even when it is null.)
            foreach (['device_number', 'mac_addr'] as $column) {
                $clash = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_virtual_machine_id', $dbVm->id)
                    ->where($column, $data[$column])
                    ->first();

                if ($clash && $clash->id != $dbVif?->id) {
                    $clash->forceDelete();
                }
            }

            try {
                if ($dbVif) {
                    $dbVif->updateQuietly($data);
                } else {
                    VirtualNetworkCardsService::create($data);
                }
            } catch (\Exception $exception) {
                Log::error('[VirtualMachineAgentSyncService@syncVirtualMachineNetworkCards] Failed to sync VIF ' .
                    $vifUuid . ' for VM ' . $dbVm->uuid . ': ' . $exception->getMessage());
            }
        }
    }

    // -- per-volume disk sync --------------------------------------------------------------------

    /**
     * Syncs every disk on one storage volume (SR) and refreshes the volume's size figures.
     */
    public static function syncStorageVolumeDisks(ComputeMembers $computeMember, StorageVolumes $volume): StorageVolumes
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        $disks = $client->call('xenserver.vdi.list', ['sr_uuid' => $volume->hypervisor_uuid]);

        foreach ($disks as $disk) {
            if (empty($disk['uuid'])) {
                continue;
            }

            $dbDisk = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $disk['uuid'])
                ->first();

            if ($dbDisk && !self::vmRowInPoolOf($computeMember, $dbDisk->iaas_virtual_machine_id)) {
                Log::error('[VirtualMachineAgentSyncService@syncStorageVolumeDisks] Disk ' . $disk['uuid'] .
                    ' is recorded on a VM of another compute pool. Skipped, needs review.');
                continue;
            }

            $diskParams = ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.vdi.param_list', ['vdi_uuid' => $disk['uuid']])
            );

            $vbdParams = null;
            $vbdUuid   = $diskParams['vbd-uuids'][0] ?? null;

            if ($vbdUuid) {
                $vbdParams = ComputeMemberAgentSyncService::flatten(
                    $client->call('xenserver.vbd.param_list', ['vbd_uuid' => $vbdUuid])
                );
            }

            $diskVolume = StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);

            if (!$diskVolume) {
                Log::warning('[VirtualMachineAgentSyncService@syncStorageVolumeDisks] Disk has no storage volume in DB, ' .
                    'running the storage volume sync for this compute member');

                ComputeMemberAgentSyncService::updateStorageVolumes($computeMember);
                $diskVolume = StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);
            }

            if (!$diskVolume) {
                Log::error('[VirtualMachineAgentSyncService@syncStorageVolumeDisks] Still no storage volume for SR ' .
                    $diskParams['sr-uuid'] . ', skipping disk ' . $diskParams['uuid']);
                continue;
            }

            $data = [
                'name'                      => $dbDisk ? $dbDisk->name : $diskParams['name-label'],
                'size'                      => $diskParams['virtual-size'],
                'physical_utilisation'      => $diskParams['physical-utilisation'],
                'iaas_storage_volume_id'    => $diskVolume->id,
                'iaas_storage_pool_id'      => $diskVolume->iaas_storage_pool_id,
                'is_cdrom'                  => false,
                'hypervisor_uuid'           => $diskParams['uuid'],
                'hypervisor_data'           => $disk,
                //  Default owner until the VM is found.
                'iam_account_id'            => $dbDisk ? $dbDisk->iam_account_id : config('leo.current_account_id'),
                'iam_user_id'               => $dbDisk ? $dbDisk->iam_user_id : config('leo.current_user_id'),
                'is_draft'                  => false,
            ];

            $vm = null;

            if ($vbdParams) {
                $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $vbdParams['vm-uuid'])
                    ->where('iaas_compute_pool_id', $computeMember->iaas_compute_pool_id)
                    ->first();

                if (!$vm) {
                    Log::warning('[VirtualMachineAgentSyncService@syncStorageVolumeDisks] Cannot find the VM with uuid: ' . $vbdParams['vm-uuid']);
                }

                $data = array_merge($data, [
                    'iaas_virtual_machine_id'   => $vm ? $vm->id : null,
                    'device_number'             => $vm ? $vbdParams['userdevice'] : null,
                    'iam_account_id'            => $vm ? $vm->iam_account_id : config('leo.current_account_id'),
                    'iam_user_id'               => $vm ? $vm->iam_user_id : config('leo.current_user_id'),
                    'vbd_hypervisor_uuid'       => $vm ? $vbdParams['uuid'] : null,
                    'vbd_hypervisor_data'       => $vm ? $vbdParams : null,
                    'created_at'                => $vm ? $vm->created_at : ($dbDisk ? $dbDisk->created_at : now()),
                ]);
            }

            //  Two disks can claim the same VM device slot between sync passes. On that unique
            //  index collision, reconcile this VM's disks against the hypervisor and retry once.
            try {
                $dbDisk ? $dbDisk->updateQuietly($data) : VirtualDiskImages::create($data);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($vm && str_contains($e->getMessage(), 'iaas_virtual_disk_images_vm_device_number_uidx')) {
                    Log::warning('[VirtualMachineAgentSyncService@syncStorageVolumeDisks] Device number conflict for VM: ' .
                        $vm->uuid . ', reconciling disk records against the hypervisor and retrying.');

                    self::reconcileVmDiskDeviceNumbers($computeMember, $vm);

                    $dbDisk ? $dbDisk->fresh()->updateQuietly($data) : VirtualDiskImages::create($data);
                } else {
                    throw $e;
                }
            }
        }

        return self::refreshVolumeSize($computeMember, $volume);
    }

    /**
     * Makes this VM's disk records match the hypervisor: disks no longer attached are detached in
     * the DB too, attached ones get their real device number.
     */
    public static function reconcileVmDiskDeviceNumbers(ComputeMembers $computeMember, VirtualMachines $vm): void
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        $vmParams = ComputeMemberAgentSyncService::flatten(
            $client->call('xenserver.vm.param_list', ['uuid' => $vm->hypervisor_uuid])
        );

        //  Authoritative (vdi uuid => device number) map from each VBD's own parameters.
        $trueDeviceNumbers = [];

        foreach (($vmParams['VBDs'] ?? []) as $vbdUuid) {
            $vbd = ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.vbd.param_list', ['vbd_uuid' => $vbdUuid])
            );

            if (!isset($vbd['vdi-uuid']) || !isset($vbd['userdevice'])) {
                continue;
            }

            $trueDeviceNumbers[$vbd['vdi-uuid']] = $vbd['userdevice'];
        }

        $dbDisks = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();

        foreach ($dbDisks as $dbDisk) {
            if (array_key_exists($dbDisk->hypervisor_uuid, $trueDeviceNumbers)) {
                $real = $trueDeviceNumbers[$dbDisk->hypervisor_uuid];

                if ($dbDisk->device_number != $real) {
                    Log::warning('[VirtualMachineAgentSyncService@reconcileVmDiskDeviceNumbers] Correcting device_number of disk ' .
                        $dbDisk->uuid . ' from ' . $dbDisk->device_number . ' to ' . $real);

                    $dbDisk->updateQuietly(['device_number' => $real]);
                }
            } else {
                Log::warning('[VirtualMachineAgentSyncService@reconcileVmDiskDeviceNumbers] Disk ' . $dbDisk->uuid .
                    ' is no longer attached to VM ' . $vm->uuid . ' on the hypervisor, detaching in DB.');

                $dbDisk->updateQuietly(['iaas_virtual_machine_id' => null, 'device_number' => null]);
            }
        }
    }

    /**
     * Refreshes total/used/virtual-allocation of the volume. When the SR cannot be read, the
     * volume is only disabled if the SR is confirmed gone from the live SR list, so a transient
     * agent error is not treated as a deletion.
     */
    private static function refreshVolumeSize(ComputeMembers $computeMember, StorageVolumes $volume): StorageVolumes
    {
        $client = ComputeMemberAgentSyncService::client($computeMember);

        try {
            $info = ComputeMemberAgentSyncService::flatten(
                $client->call('xenserver.sr.param_list', ['uuid' => $volume->hypervisor_uuid])
            );
        } catch (XenAgentException $e) {
            $info = null;
        }

        if (!$info || !isset($info['physical-size'])) {
            $srs = array_column($client->call('xenserver.sr.list'), 'uuid');

            if (!in_array($volume->hypervisor_uuid, $srs, true)) {
                Log::warning('[VirtualMachineAgentSyncService@refreshVolumeSize] Storage repository ' . $volume->hypervisor_uuid .
                    ' no longer exists on compute member: ' . $computeMember->name . ', disabling storage volume: ' . $volume->uuid);

                $volume->update(['is_alive' => false]);
            }

            return $volume->fresh();
        }

        $volume->update([
            'total_hdd'          => ceil($info['physical-size'] / 1000 / 1000 / 1000),
            'used_hdd'           => ceil($info['physical-utilisation'] / 1000 / 1000 / 1000),
            'virtual_allocation' => ceil($info['virtual-allocation'] / 1000 / 1000 / 1000),
        ]);

        return $volume->fresh();
    }

    /**
     * True when a row's compute pool is the member's own pool. A row with no pool yet (draft)
     * can be adopted.
     */
    private static function inPoolOf(ComputeMembers $computeMember, $poolId): bool
    {
        return $poolId === null || (int) $poolId === (int) $computeMember->iaas_compute_pool_id;
    }

    /**
     * Same check for a disk or card, through the VM it points to. Rows with no VM (detached
     * disks) have no owner pool to protect.
     */
    private static function vmRowInPoolOf(ComputeMembers $computeMember, $vmId): bool
    {
        if (!$vmId) {
            return true;
        }

        $vm = VirtualMachines::withoutGlobalScopes()->where('id', $vmId)->first();

        return !$vm || self::inPoolOf($computeMember, $vm->iaas_compute_pool_id);
    }

    private static function findComputeMemberVolume(ComputeMembers $computeMember, string $srUuid): ?ComputeMemberStorageVolumes
    {
        $volume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $srUuid)
            ->first();

        if (!$volume) {
            //  The SR was added after the last host sync. Run the storage sync once and look again.
            ComputeMemberAgentSyncService::updateStorageVolumes($computeMember);

            $volume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $srUuid)
                ->first();
        }

        return $volume;
    }
}
