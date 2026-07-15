<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use NextDeveloper\IAAS\Contracts\BackupCapableInterface;
use NextDeveloper\IAAS\Contracts\CloneCapableInterface;
use NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface;
use NextDeveloper\IAAS\Contracts\ConsoleCapableInterface;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Contracts\EventTranslatorCapableInterface;
use NextDeveloper\IAAS\Contracts\ExportCapableInterface;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Contracts\NetworkCapableInterface;
use NextDeveloper\IAAS\Contracts\ProvisioningCapableInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Contracts\SnapshotCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Actions\VirtualNetworkCards\Attach;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\HypervisorService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Events\XenServerEventTranslator;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAAS\ValueObjects\ConsoleSession;
use NextDeveloper\IAAS\ValueObjects\NormalizedHypervisorEvent;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Wraps the existing Hypervisors/XenServer/*Service classes (SSH + `xe` CLI, unchanged)
 * behind the driver interfaces, so Actions/Jobs/Controllers stop calling those classes
 * directly and go through VirtualMachineManager instead. This is a thin wrapper on
 * purpose - it does not change any underlying `xe` behavior, it only gives it a stable,
 * hypervisor-agnostic entry point. See docs/hypervisor-driver-architecture.md.
 *
 * Registered for "xenserver-8.2-ssh" and "xcp-ng-8.2-ssh" (XCP-ng is XAPI-compatible,
 * see config/virtualization.php) - not registered for a future "xenserver-8.2-agent"
 * variant, which will be a separate driver class per the transport decoupling in the
 * architecture doc.
 */
class XenServer82SshDriver implements
    VirtualMachineAdapterInterface,
    SnapshotCapableInterface,
    CloneCapableInterface,
    ResizeCapableInterface,
    DiskCapableInterface,
    NetworkCapableInterface,
    HostSyncInterface,
    ConsoleCapableInterface,
    EventTranslatorCapableInterface,
    ExportCapableInterface,
    ConfigurationIsoCapableInterface,
    ProvisioningCapableInterface,
    BackupCapableInterface
{
    public function __construct(private readonly array $config = [])
    {
    }

    // -- VirtualMachineAdapterInterface ----------------------------------------------

    public function start(VirtualMachines $vm): VirtualMachines
    {
        VirtualMachinesXenService::start($vm);

        return $this->sync($vm);
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $force ? VirtualMachinesXenService::forceShutdown($vm) : VirtualMachinesXenService::shutdown($vm);

        //  xe vm-shutdown can return before the guest has actually finished powering off -
        //  a graceful ACPI shutdown waits on the guest OS to respond, which isn't
        //  instantaneous. A single immediate sync() right after issuing the command can
        //  catch the VM still mid-transition and falsely report a failed shutdown, even
        //  though it reaches 'halted' moments later. Poll briefly instead of trusting one
        //  snapshot in time.
        return $this->syncUntilPowerState($vm, 'halted');
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $force ? VirtualMachinesXenService::forceRestart($vm) : VirtualMachinesXenService::restart($vm);

        return $this->sync($vm);
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        VirtualMachinesXenService::pause($vm);

        return $this->sync($vm);
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        //  XenServer calls this "unpause" - the interface uses the intent-level name.
        VirtualMachinesXenService::unpause($vm);

        return $this->sync($vm);
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Suspend is not implemented for XenServer in this driver yet - ' .
            'there is no existing suspend/checkpoint-to-disk operation in VirtualMachinesXenService to wrap.');
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        return VirtualMachinesXenService::getVmParameters($vm);
    }

    public function delete(VirtualMachines $vm): bool
    {
        $result = VirtualMachinesXenService::destroyVm($vm);

        return empty($result['error']);
    }

    public function sync(VirtualMachines $vm): VirtualMachines
    {
        $params = VirtualMachinesXenService::getVmParameters($vm);

        if (!$params || !array_key_exists('power-state', $params)) {
            //  Still write hypervisor_data even on a no-op sync, so callers checking
            //  array_key_exists('power-state', $vm->hypervisor_data) after this call see
            //  THIS call's fresh (empty/partial) response, not a stale one from a
            //  previous successful sync that would otherwise mask an unreachable VM.
            $vm->update(['hypervisor_data' => $params ?: []]);

            return $vm->fresh();
        }

        $vm->update([
            'status'            =>  $params['power-state'],
            'cpu'               =>  $params['VCPUs-max'] ?? $vm->cpu,
            'ram'               =>  isset($params['memory-static-max']) ? $params['memory-static-max'] / 1024 / 1024 / 1024 : $vm->ram,
            'is_snapshot'       =>  ($params['is-a-snapshot'] ?? 'false') === 'true',
            'domain_type'       =>  ($params['hvm'] ?? 'false') === 'true' ? 'HVM' : 'PV',
            'hypervisor_data'   =>  $params,
        ]);

        return $vm->fresh();
    }

    /**
     * Polls sync() until the VM reports the expected power-state or the attempt budget
     * runs out, instead of trusting a single immediate check right after issuing a power
     * command - XenServer doesn't guarantee the state transition is complete by the time
     * the triggering `xe` command returns. Non-throwing: if the state is never reached,
     * returns the last synced (still-not-matching) state so the caller's own
     * status-check logic decides what "still not there" means.
     */
    private function syncUntilPowerState(VirtualMachines $vm, string $expectedState, int $maxAttempts = 5, int $delaySeconds = 2): VirtualMachines
    {
        $vm = $this->sync($vm);

        for ($attempt = 1; $attempt < $maxAttempts && $vm->status !== $expectedState; $attempt++) {
            sleep($delaySeconds);
            $vm = $this->sync($vm);
        }

        return $vm;
    }

    public function listAll(): array
    {
        throw new \RuntimeException('listAll() is not scoped in this driver - VirtualMachinesXenService ' .
            'has no host/pool-wide "list every vm" call today; use HostSyncInterface::syncVirtualMachines() ' .
            'for a given compute member instead.');
    }

    // -- SnapshotCapableInterface -----------------------------------------------------

    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null): VirtualMachines
    {
        $snapshot = VirtualMachinesXenService::takeSnapshot($vm, $name);

        if (!empty($snapshot['error'])) {
            throw new \RuntimeException('Failed to take snapshot: ' . $snapshot['error']);
        }

        return VirtualMachinesService::create([
            'snapshot_of_virtual_machine'   =>  $vm->id,
            'name'                          =>  $name,
            'hypervisor_uuid'               =>  $snapshot['output'],
            'is_snapshot'                   =>  true,
            'is_draft'                      =>  false,
            'os'                            =>  $vm->os,
            'distro'                        =>  $vm->distro,
            'version'                       =>  $vm->version,
            'status'                        =>  'halted',
            'cpu'                           =>  $vm->cpu,
            'ram'                           =>  $vm->ram,
            'auto_backup_interval'          =>  'none',
            'auto_backup_time'              =>  'none',
            'iaas_compute_pool_id'          =>  $vm->iaas_compute_pool_id,
            'iaas_compute_member_id'        =>  $vm->iaas_compute_member_id,
            'iaas_cloud_node_id'            =>  $vm->iaas_cloud_node_id,
        ]);
    }

    public function deleteSnapshot(VirtualMachines $vm, string $snapshotId): bool
    {
        $snapshot = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $snapshotId)
            ->first();

        if (!$snapshot) {
            return false;
        }

        return $this->delete($snapshot);
    }

    public function restoreSnapshot(VirtualMachines $vm, string $snapshotId): bool
    {
        $snapshot = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $snapshotId)
            ->first();

        if (!$snapshot) {
            return false;
        }

        $result = VirtualMachinesXenService::convertSnapshotToVm($snapshot);

        return empty($result['error']);
    }

    public function listSnapshots(VirtualMachines $vm): array
    {
        return VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('snapshot_of_virtual_machine', $vm->id)
            ->where('is_snapshot', true)
            ->get()
            ->all();
    }

    public function convertSnapshotToVm(VirtualMachines $vm, ?string $name = null): array
    {
        return VirtualMachinesXenService::convertSnapshotToVm($vm, $name);
    }

    // -- CloneCapableInterface ---------------------------------------------------------

    public function clone(VirtualMachines $vm, string $newName, array $options = []): VirtualMachines
    {
        $result = VirtualMachinesXenService::cloneVm($vm);

        if (!empty($result['error'])) {
            throw new \RuntimeException('Failed to clone vm: ' . $result['error']);
        }

        return VirtualMachinesService::create([
            'name'                      =>  $newName,
            'hypervisor_uuid'           =>  $result['output'],
            'is_draft'                  =>  false,
            'os'                        =>  $vm->os,
            'distro'                    =>  $vm->distro,
            'version'                   =>  $vm->version,
            'status'                    =>  'halted',
            'cpu'                       =>  $vm->cpu,
            'ram'                       =>  $vm->ram,
            'iaas_compute_pool_id'      =>  $vm->iaas_compute_pool_id,
            'iaas_compute_member_id'    =>  $vm->iaas_compute_member_id,
            'iaas_cloud_node_id'        =>  $vm->iaas_cloud_node_id,
        ]);
    }

    // -- ResizeCapableInterface ---------------------------------------------------------

    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines
    {
        VirtualMachinesXenService::setCPUCore($vm, $cpu, $corePerSocket);
        VirtualMachinesXenService::setRam($vm, $ramInMb);

        $vm->update(['cpu' => $cpu, 'ram' => $ramInMb]);

        return $vm->fresh();
    }

    // -- DiskCapableInterface ------------------------------------------------------------

    public function createDisk(VirtualDiskImages $vdi): VirtualDiskImages
    {
        return VirtualDiskImageXenService::create($vdi);
    }

    public function attachDisk(VirtualDiskImages $vdi): VirtualDiskImages
    {
        return VirtualDiskImageXenService::attach($vdi);
    }

    public function detachDisk(VirtualDiskImages $vdi): VirtualDiskImages
    {
        return VirtualDiskImageXenService::detach($vdi);
    }

    public function destroyDisk(VirtualDiskImages $vdi): bool
    {
        $computeMember = $this->computeMemberForDisk($vdi);

        if ($vdi->vbd_hypervisor_data) {
            VirtualDiskImageXenService::detach($vdi);
        }

        VirtualDiskImageXenService::destroyDisk($vdi->hypervisor_uuid, $computeMember);

        return true;
    }

    public function resizeDisk(VirtualDiskImages $vdi, int $sizeInBytes): VirtualDiskImages
    {
        $computeMember = $this->computeMemberForDisk($vdi);

        VirtualDiskImageXenService::resize($vdi->hypervisor_uuid, $computeMember, $sizeInBytes);

        $vdi->update(['size' => $sizeInBytes]);

        return $vdi->fresh();
    }

    private function computeMemberForDisk(VirtualDiskImages $vdi): ?ComputeMembers
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->find($vdi->iaas_virtual_machine_id);

        if (!$vm) {
            return null;
        }

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->find($vm->iaas_compute_member_id);
    }

    public function mountCd(VirtualMachines $vm, RepositoryImages $image): bool
    {
        return VirtualMachinesXenService::mountCD($vm, $image);
    }

    public function unmountCd(VirtualMachines $vm): bool
    {
        VirtualMachinesXenService::unmountCD($vm);

        return true;
    }

    public function syncDiskFromHypervisor(VirtualDiskImages $vdi): VirtualDiskImages
    {
        $computeMember = VirtualDiskImagesService::getComputeMember($vdi);

        $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($vdi['hypervisor_uuid'], $computeMember);
        $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($vdi['uuid'], $computeMember);

        $volume = StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);

        if (!$volume) {
            Log::warning('[XenServer82SshDriver@syncDiskFromHypervisor] Disk does not have storage volume in DB. ' .
                'We should start storage volume sync for this compute member');

            ComputeMemberXenService::updateStorageVolumes($computeMember);
        }

        $data = [
            'size' => $diskParams['virtual-size'],
            'physical_utilisation' => $diskParams['physical-utilisation'],
            'hypervisor_data' => $diskParams,
            'is_draft' => false,
            'vbd_hypervisor_data'   =>  $vbdParams,
            'vbd_hypervisor_uuid'   =>  $vbdParams['uuid'],
            'iaas_storage_volume_id' =>  $volume->id,
            'iaas_storage_pool_id'  =>  $volume->iaas_storage_pool_id,
        ];

        $vdi->updateQuietly($data);

        return $vdi->fresh();
    }

    // -- NetworkCapableInterface --------------------------------------------------------

    public function createNetworkCard(VirtualMachines $vm, string $networkUuid, int $device): VirtualNetworkCards
    {
        $vifUuid = VirtualMachinesXenService::createVif($vm, $networkUuid, $device);

        return VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $vifUuid)
            ->firstOrFail();
    }

    public function destroyNetworkCard(VirtualNetworkCards $vif): bool
    {
        $vm = VirtualNetworkCardsXenService::getVirtualMachine($vif);

        if (!$vm) {
            return false;
        }

        return VirtualMachinesXenService::destroyVif($vm, $vif->hypervisor_uuid);
    }

    public function syncNetworkCard(VirtualNetworkCards $vif): VirtualNetworkCards
    {
        VirtualNetworkCardsXenService::sync($vif);

        return $vif->fresh();
    }

    public function applyIpFilter(VirtualNetworkCards $vif): bool
    {
        VirtualNetworkCardsXenService::setIpv4Allowed($vif);

        return true;
    }

    public function setIpFilterMode(VirtualNetworkCards $vif, string $mode): bool
    {
        VirtualNetworkCardsXenService::setLockingState($vif, $mode);

        return true;
    }

    public function attachDraftNetworkCard(VirtualNetworkCards $vif): VirtualNetworkCards
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vif->iaas_virtual_machine_id)
            ->first();

        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vif->iaas_network_id)
            ->first();

        $interface = ComputeMemberXenService::createNetwork($computeMember, $network);

        $networkCardResult = VirtualMachinesXenService::createVif($vm, $interface->network_uuid, $vif->device_number);

        if (!Str::isUuid($networkCardResult)) {
            $error = is_array($networkCardResult) ? ($networkCardResult[0]['error'] ?? 'unknown error') : 'unknown error';

            throw new \RuntimeException('Failed to create the network card on the hypervisor: ' . $error);
        }

        $vifParams = VirtualMachinesXenService::getVifParams($vm, $networkCardResult);
        $vifParams = $vifParams[0];

        $data = [
            'name'          =>  'eth' . $vifParams['device'],
            'device_number' => $vifParams['device'],
            'mac_addr'      => $vifParams['MAC'],
            'bandwidth_limit'   => '-1', //$vifParams['qos_algorithm_params']['kbps'],
            'iaas_network_id'       => $network->id,
            'hypervisor_uuid'   => $vif['uuid'],
            'hypervisor_data'   => $vifParams,
            'iam_account_id'    => $vm->iam_account_id,
            'iam_user_id'       => $vm->iam_user_id,
            'is_draft'          => false,
            'iaas_virtual_machine_id'   =>  $vm->id,
            'status'    =>  'attached:' . $vifParams['currently-attached']
        ];

        $vif->update($data);

        return $vif->fresh();
    }

    // -- HostSyncInterface ---------------------------------------------------------------

    public function detectVersion(ComputeMembers $computeMember): string
    {
        $detected = HypervisorService::getHypervisor($computeMember);

        //  XCP-ng shares this entire driver (same XAPI/`xe` surface) - config/virtualization.php
        //  tags it with a 'product' override so display/diagnostics can tell it apart from
        //  XenServer even though dispatch never needs to (both resolve to this same class).
        if (!empty($this->config['product'])) {
            return $this->config['product'] . ' (' . $detected . ')';
        }

        return $detected;
    }

    public function syncMember(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberXenService::updateMemberInformation($computeMember);
    }

    public function syncInterfaces(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberXenService::updateInterfaceInformation($computeMember);
    }

    public function syncNetworks(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberXenService::updateNetworkInformation($computeMember);
    }

    public function syncStorageVolumes(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberXenService::updateStorageVolumes($computeMember);
    }

    public function syncVirtualMachines(ComputeMembers $computeMember): ComputeMembers
    {
        $virtualMachines = ComputeMemberXenService::getListOfVirtualMachines($computeMember);

        $vmCount = count($virtualMachines);

        Log::info('[XenServer82SshDriver@syncVirtualMachines] Found ' . $vmCount .
            ' virtual machines on compute member: ' . $computeMember->name);

        for ($i = 0; $i < $vmCount; $i++) {
            $vm = $virtualMachines[$i];

            //  A malformed/empty hypervisor response (e.g. a compute member returning no
            //  usable output for this entry) can produce a record with no uuid. Skip it
            //  outright - without this guard, syncVirtualMachineRecord()'s
            //  where('hypervisor_uuid', $vm['uuid']) lookup below would silently become
            //  Laravel's whereNull('hypervisor_uuid') for a null/missing uuid, matching
            //  and overwriting an arbitrary unrelated draft VM elsewhere in the database
            //  with this empty record's data.
            if (empty($vm['uuid'])) {
                Log::warning('[XenServer82SshDriver@syncVirtualMachines] Skipping a malformed VM entry with no uuid from compute member: ' . $computeMember->name);
                continue;
            }

            Log::info('[XenServer82SshDriver@syncVirtualMachines] Scanning virtual machine number: ' .
                $i . ' [' . $vm['name-label'] . '] / [' . $vm['uuid'] . ']');

            $vmInfo = ComputeMemberXenService::getVirtualMachineByUuid($computeMember, $vm['uuid']);

            //  We are skipping the scan of virtual machines which has "migrated_" in the name
            //  Because if we don't skip we will be having two servers
            if (Str::startsWith($vmInfo[0]['name-label'], 'exported_')) {
                continue;
            }

            if (is_array($vmInfo) && array_key_exists('error', $vmInfo)) {
                Log::error('[XenServer82SshDriver@syncVirtualMachines] Error while scanning virtual machine: ' . $vm['uuid']);
                Log::error($vmInfo);
                continue;
            }

            if (is_array($vmInfo)) {
                $vmInfo = $vmInfo[0];
            }

            $dbVm = $this->syncVirtualMachineRecord($computeMember, $vm, $vmInfo);

            $this->syncVirtualMachineDisks($computeMember, $dbVm);
            $this->syncVirtualMachineNetworkCards($computeMember, $dbVm);
        }

        return $computeMember->fresh();
    }

    /**
     * Upserts the VirtualMachines DB row for one hypervisor-reported VM (create if we've
     * never seen this hypervisor_uuid before, update otherwise).
     */
    private function syncVirtualMachineRecord(ComputeMembers $computeMember, array $vm, array $vmInfo): VirtualMachines
    {
        $dbVm = VirtualMachines::withoutGlobalScopes()->where('hypervisor_uuid', $vm['uuid'])->first();

        $computePool = ComputePools::withoutGlobalScopes()->where('id', $computeMember->iaas_compute_pool_id)->first();
        $cloudNode = CloudNodes::withoutGlobalScopes()->where('id', $computePool->iaas_cloud_node_id)->first();

        if ($dbVm) {
            $dbVm->updateQuietly([
                'domain_type'   =>  $vmInfo['hvm'] == 'false' ? 'pv' : 'hvm',
                'cpu'           =>  $vmInfo['VCPUs-max'],
                'ram'           =>  $vmInfo['memory-static-max'] / 1024 / 1024, //  this comes in bytes, converting to MB,
                'status'        =>  $vmInfo['power-state'],
                //  Merge so that other keys (e.g. 'agent') set by other sources are not overwritten
                'available_operations'  =>  array_merge(
                    $dbVm->available_operations ?? [],
                    ['hypervisor' => $vmInfo['allowed-operations']]
                ),
                'current_operations'    =>  $vmInfo['current-operations'],
                'blocked_operations'    =>  $vmInfo['blocked-operations'],
                'hypervisor_uuid'       =>  $vm['uuid'],
                'hypervisor_data'       =>  $vmInfo,
                'is_draft'              =>  false,
                'iaas_compute_member_id'    =>  $computeMember->id,
                'iaas_cloud_node_id'        =>  $cloudNode->id,
                'iaas_compute_pool_id'      =>  $computePool->id
            ]);

            $dbVm = $dbVm->fresh();

            if (config('iaas.regulations.pci_dss.change_names')) {
                $isChanged = false;

                if (!$isChanged) {
                    Log::error('[XenServer82SshDriver@syncVirtualMachines] Error while renaming virtual machine: ' . $vm['uuid']);
                    StateHelper::setState($computeMember, 'host_change_rename_error', 'true');
                }
            }
        } else {
            $dbVm = VirtualMachines::create([
                'name'          =>  $vmInfo['name-label'],
                'domain_type'   =>  $vmInfo['hvm'] == 'false' ? 'pv' : 'hvm',
                'cpu'           =>  $vmInfo['VCPUs-max'],
                'ram'           =>  $vmInfo['memory-static-max'] / 1024 / 1024, //  this comes in bytes, converting to MB,
                'status'        =>  $vmInfo['power-state'],
                'available_operations'  =>  ['hypervisor' => $vmInfo['allowed-operations']],
                'current_operations'    =>  $vmInfo['current-operations'],
                'blocked_operations'    =>  $vmInfo['blocked-operations'],
                'hypervisor_uuid'       =>  $vm['uuid'],
                'hypervisor_data'       =>  $vmInfo,
                'is_draft'              =>  false,
                'iaas_compute_member_id'    =>  $computeMember->id,
                'iaas_cloud_node_id'        =>  $cloudNode->id,
                'iaas_compute_pool_id'      =>  $computePool->id,
                'iam_account_id'            =>  $computeMember->iam_account_id,
                'iam_user_id'               =>  $computeMember->iam_user_id
            ]);

            if (config('iaas.regulations.pci_dss.change_names')) {
                $isChanged = false;

                if (!$isChanged) {
                    Log::error('[XenServer82SshDriver@syncVirtualMachines] Error while renaming virtual machine: ' . $vm['uuid']);
                    StateHelper::setState($computeMember, 'host_change_rename_error', 'true');
                }
            }
        }

        return $dbVm;
    }

    /**
     * Upserts the VirtualDiskImages rows for one VM's VBDs/VDIs as reported by the
     * hypervisor (including the CDROM slot, if any).
     */
    private function syncVirtualMachineDisks(ComputeMembers $computeMember, VirtualMachines $dbVm): void
    {
        $vbds = VirtualMachinesXenService::getVmDisks($dbVm);

        foreach ($vbds as $vbd) {
            //  Sometimes we get null values, we are skipping them (I dont know why)
            if ($vbd == []) {
                continue;
            }

            if (array_key_exists('vdi-uuid', $vbd)) {
                $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($vbd['vdi-uuid'], $computeMember);
            }

            $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($vbd['uuid'], $computeMember);

            //  We are taking CDROM if the vbd type is CDROM
            if ($vbdParams['type'] === 'CD') {
                $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('is_cdrom', true)
                    ->where('iaas_virtual_machine_id', $dbVm->id)
                    ->first();
            } else {
                $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $diskParams['uuid'])
                    ->first();

                //  If we found a VDI by hypervisor_uuid but it belongs to a different VM,
                //  check whether the current VM already has its own record for this disk
                //  (e.g. created by a migration clone). If so, use that record instead to
                //  avoid a unique constraint violation on (iaas_virtual_machine_id, device_number).
                if ($dbVdi && (int) $dbVdi->iaas_virtual_machine_id !== (int) $dbVm->id) {
                    $ownRecord = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                        ->where('iaas_virtual_machine_id', $dbVm->id)
                        ->where('device_number', $vbdParams['userdevice'])
                        ->first();

                    if ($ownRecord) {
                        //  The current VM already owns a disk at this device position - use it.
                        $dbVdi = $ownRecord;
                    }
                    //  else: no own record yet, fall through and let the update reassign the
                    //  existing record to this VM (original VM no longer owns it on XenServer).

                    if ($dbVdi) {
                        Log::warning('[XenServer82SshDriver@syncVirtualMachines] There is a disk in the same ' .
                            'device_number in this virtual machine: ' . $dbVm->uuid . ', but their ' .
                            'hypervisor_uuid does not match. So it mush be updated or changed manually.');
                    }
                }
            }

            //  We are taking the volume if the VDI is CDROM
            if ($vbdParams['type'] !== 'CD') {
                $diskVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $diskParams['sr-uuid'])
                    ->first();

                if (!$diskVolume) {
                    //  This means that there is a volume but we cannot find it. We need to make sync of this Volume
                }
            }

            $data = [
                'name'                      =>  $vbdParams['type'] !== 'CD' ? 'Disk of: ' . $dbVm->name : 'CDROM',
                'size'                      =>  $vbdParams['type'] !== 'CD' ? $diskParams['virtual-size'] : 0,
                'physical_utilisation'      =>  $vbdParams['type'] !== 'CD' ? $diskParams['physical-utilisation'] : 0,
                'iaas_storage_volume_id'    =>  $vbdParams['type'] !== 'CD' ? $diskVolume->iaas_storage_volume_id : null,
                'iaas_virtual_machine_id'   =>  $dbVm->id,
                'device_number'             =>  $vbdParams['userdevice'],
                'is_cdrom'                  =>  $vbdParams['type'] === 'CD',
                'hypervisor_uuid'       =>  $vbdParams['vdi-uuid'],
                'hypervisor_data'       =>  $diskParams ?? [],
                'iam_account_id'        =>  $dbVm->iam_account_id,
                'iam_user_id'           =>  $dbVm->iam_user_id,
                'is_draft'              =>  false,
                'vbd_hypervisor_uuid'   =>  $vbd['uuid'],
                'vbd_hypervisor_data'   =>  $vbdParams
            ];

            if ($dbVdi) {
                $dbVdi->updateQuietly($data);
            } else {
                //  We need to check if we already have a record with the iaas_virtual_machine_id and device_number
                //  If we have, we will update it, if not we will create a new one
                $checkVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_virtual_machine_id', $dbVm->id)
                    ->where('device_number', $vbdParams['userdevice'])
                    ->withTrashed()
                    ->first();

                if ($checkVdi) {
                    if ($checkVdi->trashed()) {
                        $checkVdi->restore();
                    }
                }

                //  This happens when the VDI is migrated to another storage. We need to update the hypervisor_uuid
                if ($checkVdi) {
                    $checkVdi->updateQuietly($data);
                    $dbVdi = $checkVdi;
                } else {
                    $dbVdi = VirtualDiskImages::create($data);
                }
            }
        }
    }

    /**
     * Upserts the VirtualNetworkCards rows for one VM's VIFs as reported by the
     * hypervisor.
     */
    private function syncVirtualMachineNetworkCards(ComputeMembers $computeMember, VirtualMachines $dbVm): void
    {
        $computePool = ComputePools::withoutGlobalScopes()->where('id', $computeMember->iaas_compute_pool_id)->first();

        $vifs = VirtualMachinesXenService::getVifs($dbVm);

        foreach ($vifs as $vif) {
            if ($vif == []) {
                continue;
            }

            $vifParams = VirtualMachinesXenService::getVifParams($dbVm, $vif['uuid']);

            if (array_key_exists(0, $vifParams)) {
                $vifParams = $vifParams[0];
            }

            $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $vif['uuid'])
                ->first();

            if (!$dbVif) {
                $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_virtual_machine_id', $dbVm->id)
                    ->where('mac_addr', $vifParams['MAC'])
                    ->withTrashed()
                    ->first();

                if ($dbVif) {
                    if ($dbVif->trashed()) {
                        $dbVif->restore();
                    }
                }
            }

            $connectedInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('network_uuid', $vifParams['network-uuid'])
                ->first();

            if (!$connectedInterface) {
                //  Here we will add another trigger to scan all compute member network interfaces
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[XenServer82SshDriver@syncVirtualMachines] Cannot find the connected ' .
                    'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                    'should be scanned and synced immediately.');

                continue;
            }

            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $connectedInterface->vlan)
                ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                ->first();

            if (!$network) {
                //  Here we need to create another scan and create the related network
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[XenServer82SshDriver@syncVirtualMachines] Cannot find the connected ' .
                    'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                    'should be scanned and synced immediately.');

                continue;
            }

            $data = [
                'name'          =>  'eth' . $vifParams['device'],
                'device_number' => $vifParams['device'],
                'mac_addr'      => $vifParams['MAC'],
                'bandwidth_limit'   => '-1', //$vifParams['qos_algorithm_params']['kbps'],
                'iaas_network_id'       => $network->id,
                'hypervisor_uuid'   => $vif['uuid'],
                'hypervisor_data'   => $vifParams,
                'iam_account_id'    => $dbVm->iam_account_id,
                'iam_user_id'       => $dbVm->iam_user_id,
                'is_draft'          => false,
                'status'            => 'attached:true',
                'iaas_virtual_machine_id'   =>  $dbVm->id
            ];

            //  Check if there is another VIF on same device
            $vifOnSameDevice = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $dbVm->id)
                ->where('device_number', $data['device_number'])
                ->first();

            if ($vifOnSameDevice) {
                if ($vifOnSameDevice->id != $dbVif->id) {
                    $vifOnSameDevice->forceDelete();
                }
            }

            $vifOnSameMac = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $dbVm->id)
                ->where('mac_addr', $data['mac_addr'])
                ->first();

            if ($vifOnSameMac) {
                if ($vifOnSameMac->id != $dbVif->id) {
                    $vifOnSameMac->forceDelete();
                }
            }

            try {
                if ($dbVif) {
                    $dbVif->updateQuietly($data);
                } else {
                    VirtualNetworkCardsService::create($data);
                }
            } catch (\Exception $exception) {
                //  Was dump()/dd() in the original Action code - dd() would have killed the
                //  whole scan job on the first VIF sync error. Logging instead so one bad
                //  VIF doesn't take down the rest of the host scan.
                Log::error('[XenServer82SshDriver@syncVirtualMachines] Failed to sync VIF ' .
                    $vif['uuid'] . ' for VM ' . $dbVm->uuid . ': ' . $exception->getMessage());
            }
        }
    }

    public function syncStorageVolumeDisks(ComputeMembers $computeMember, StorageVolumes $volume): StorageVolumes
    {
        $disks = ComputeMemberXenService::getListOfDisksOnVolume($computeMember, $volume);

        foreach ($disks as $disk) {
            if (!array_key_exists('uuid', $disk)) {
                continue;
            }

            Log::info('[XenServer82SshDriver@syncStorageVolumeDisks] Syncing disk: ' . $disk['uuid']);

            $dbDisk = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $disk['uuid'])
                ->first();

            $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($disk['uuid'], $computeMember);

            $vbdParams = null;

            if ($diskParams['vbd-uuids']) {
                $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($diskParams['vbd-uuids'], $computeMember);
            }

            $diskVolume = StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);

            if (!$diskVolume) {
                Log::warning('[XenServer82SshDriver@syncStorageVolumeDisks] Disk does not have storage volume ' .
                    'in DB. We should start storage volume sync for this compute member');

                ComputeMemberXenService::updateStorageVolumes($computeMember);
            }

            $data = [
                'name' => $dbDisk ? $dbDisk->name : $diskParams['name-label'],
                'size' => $diskParams['virtual-size'],
                'physical_utilisation' => $diskParams['physical-utilisation'],
                'iaas_storage_volume_id' => $diskVolume->id,
                'iaas_storage_pool_id' => $diskVolume->iaas_storage_pool_id,
                'is_cdrom' => false,
                'hypervisor_uuid' => $diskParams['uuid'],
                'hypervisor_data' => $disk,
                //  here we are adding default user for the disks. If we can find the vm, then we will change it.
                'iam_account_id' => $dbDisk ? $dbDisk->iam_account_id : config('leo.current_account_id'),
                'iam_user_id' => $dbDisk ? $dbDisk->iam_user_id : config('leo.current_user_id'),
                'is_draft' => false,
            ];

            if ($vbdParams) {
                $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $vbdParams['vm-uuid'])
                    ->first();

                if (!$vm) {
                    Log::warning('[XenServer82SshDriver@syncStorageVolumeDisks] We cannot find the VM with uuid: ' . $vbdParams['vm-uuid']);
                }

                $data = array_merge($data, [
                    'iaas_virtual_machine_id' => $vm ? $vm->id : null,
                    'device_number' => $vm ? $vbdParams['userdevice'] : null,
                    'iam_account_id' => $vm ? $vm->iam_account_id : config('leo.current_account_id'),
                    'iam_user_id' => $vm ? $vm->iam_user_id : config('leo.current_user_id'),
                    'vbd_hypervisor_uuid' => $vm ? $vbdParams['uuid'] : null,
                    'vbd_hypervisor_data' => $vm ? $vbdParams : null,
                ]);

                if ($dbDisk) {
                    $data['created_at'] = $vm ? $vm->created_at : $dbDisk->created_at;
                } else {
                    $data['created_at'] = $vm ? $vm->created_at : now();
                }
            }

            if (!$dbDisk) {
                VirtualDiskImages::create($data);
            } else {
                $dbDisk->updateQuietly($data);
            }
        }

        $volumeInfo = ComputeMemberXenService::getStorageVolumeInformationByHypervisorUuid($computeMember, $volume->hypervisor_uuid);

        $volume->update([
            'total_hdd'         =>  ceil($volumeInfo['physical-size'] / 1000 / 1000 / 1000),
            'used_hdd'          =>  ceil($volumeInfo['physical-utilisation'] / 1000 / 1000 / 1000),
            'virtual_allocation' =>  ceil($volumeInfo['virtual-allocation'] / 1000 / 1000 / 1000),
        ]);

        return $volume->fresh();
    }

    public function isPoolMember(ComputeMembers $computeMember): bool
    {
        //  XAPI always has exactly one pool object, even for a standalone host - the
        //  practical signal for "is this a real, multi-host pool" is host count. This
        //  codebase has never issued pool-join/pool-eject itself (confirmed - no pool-*
        //  commands anywhere), so a real pool here only exists if an operator joined
        //  hosts by hand outside of this platform.
        $result = ComputeMemberXenService::performCommand('xe host-list --minimal', $computeMember);

        if (!$result || empty($result['output'])) {
            return false;
        }

        $hostUuids = array_filter(explode(',', trim($result['output'])));

        return count($hostUuids) > 1;
    }

    // -- ConsoleCapableInterface ---------------------------------------------------------

    public function getConsoleUrl(VirtualMachines $vm): ConsoleSession
    {
        //  Delegates entirely to the existing, working implementation (session-ref
        //  auth against XenServer 8.2+'s console proxy, redis-backed short-lived
        //  session key) rather than reimplementing it - see VirtualMachinesService
        //  for why this needs a hand-rolled XML-RPC call instead of ext-xmlrpc.
        $data = VirtualMachinesService::getConsoleDataWithSessionRef($vm);

        return new ConsoleSession(
            url: $data['service'] ?? '',
            protocol: 'xenserver-session-proxy',
            extra: $data,
        );
    }

    // -- EventTranslatorCapableInterface --------------------------------------------------

    public function translate(mixed $rawEvent): NormalizedHypervisorEvent
    {
        return XenServerEventTranslator::translate($rawEvent);
    }

    // -- ExportCapableInterface -----------------------------------------------------------

    public function exportToRepository(VirtualMachines $vm, Repositories $repository): string
    {
        return VirtualMachinesXenService::export($vm, $repository);
    }

    // -- ConfigurationIsoCapableInterface ---------------------------------------------

    public function regenerateConfigurationIso(VirtualMachines $vm): bool
    {
        return VirtualMachinesXenService::updateConfigurationIso($vm);
    }

    // -- ProvisioningCapableInterface ---------------------------------------------------

    public function mountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        return ComputeMemberXenService::mountVmRepository($computeMember, $repository);
    }

    public function unmountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        return ComputeMemberXenService::unmountVmRepository($computeMember, $repository);
    }

    public function mountIsoRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        return ComputeMemberXenService::mountIsoRepository($computeMember, $repository);
    }

    public function importFromImage(
        VirtualMachines $vm,
        ComputeMembers $computeMember,
        Repositories $repository,
        StorageVolumes $volume,
        RepositoryImages $image,
        bool $isLazyDeploy
    ): string {
        $this->mountRepository($computeMember, $repository);

        if ($isLazyDeploy) {
            return ComputeMemberXenService::importVirtualMachine(
                computeMember: $computeMember,
                volume: $volume,
                image: $image,
                vm: $vm,
                isLazyDeploy: $isLazyDeploy,
                vmUuid: $vm->uuid
            );
        }

        return ComputeMemberXenService::importVirtualMachine(
            computeMember: $computeMember,
            volume: $volume,
            image: $image,
            vm: $vm,
        );
    }

    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        return VirtualMachinesXenService::getVmParametersByUuid($computeMember, $ref);
    }

    public function renameVirtualMachine(VirtualMachines $vm): bool
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        return ComputeMemberXenService::renameVirtualMachine($computeMember, $vm);
    }

    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        return ComputeMemberXenService::setVmXenstoreData($key, $value, $vm, $computeMember);
    }

    public function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        ComputeMemberXenService::renameVirtualMachine($computeMember, $vm);

        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        //  Check if imported VM has a disk already
        $disks = VirtualMachinesXenService::getVmDisks($vm);

        $syncedDisks = [];

        foreach ($disks as $disk) {
            $connectionParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);

            foreach ($diskConfig as $config) {
                //  If the userdevice and device_number are equal, we will sync this disk.
                if ($connectionParams['userdevice'] == $config['device_number']) {
                    $this->syncDiskConfig($vm, $config, $disk);
                    $syncedDisks[] = $disk['uuid'];
                }
            }
        }

        $unsyncedDisks = [];

        foreach ($disks as $disk) {
            if (!in_array($disk['uuid'], $syncedDisks)) {
                $unsyncedDisks[] = $disk;
            }
        }

        foreach ($unsyncedDisks as $disk) {
            if ($disk['vdi-uuid'] === '<not in database>') {
                VirtualDiskImageXenService::destroyCdrom($vm->uuid, $computeMember);
            } else {
                VirtualDiskImageXenService::destroyDisk($disk['vdi-uuid'], $computeMember);
            }
        }

        //  After we finish syncing the disks, we will check if we have any disk configuration that is not synced.
        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        //  Now we need to create the disks that are in draft state
        foreach ($diskConfig as $disk) {
            //  If we have a draft disk this means that we have a disk that we need to create
            if ($disk->is_draft) {
                $disk = VirtualDiskImageXenService::create($disk);
                $disk = VirtualDiskImageXenService::attach($disk);

                //  Normal update (not quiet) so observers/events fire as usual; runAsAdmin because this
                //  runs in a queued action with no authenticated user, and the observer's updating()
                //  hook requires UserHelper::can('update', ...) to pass.
                UserHelper::runAsAdmin(function () use ($disk) {
                    $disk->update([
                        'is_draft'  =>  false
                    ]);
                });
            }
        }
    }

    private function syncDiskConfig(VirtualMachines $vm, $config, $disk): void
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        //  We are making the resize first because we need to get the disk parameters after the resize.
        //  And from there we will understand if the disk is resized or not.
        $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);

        //  If this is not a CDROM
        if ($vbdParams['type'] != 'CD') {
            VirtualDiskImageXenService::resize($disk['vdi-uuid'], $computeMember, $config->size);
            $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);
        }

        $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($disk['vdi-uuid'], $computeMember);

        $diskVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $diskParams['sr-uuid'])
            ->first();

        if ($vbdParams['type'] != 'CD') {
            //  This means that this is not a CDROM. If this is a cdrom we don't need to check the size.
            if ($config->size != $diskParams['virtual-size']) {
                StateHelper::setState($config, 'disk_cannot_resized', 'Disk cannot resized. Current size is: ' . $diskParams['virtual-size'], 'warn');
            }
        }

        $data = [
            'name' => $vbdParams['type'] !== 'CD' ? $config->name : 'CDROM',
            'size' => $vbdParams['type'] !== 'CD' ? $diskParams['virtual-size'] : 0,
            'physical_utilisation' => $vbdParams['type'] !== 'CD' ? $diskParams['physical-utilisation'] : 0,
            'iaas_storage_volume_id' => $vbdParams['type'] === 'CD' ? null : $diskVolume->iaas_storage_volume_id,
            'iaas_virtual_machine_id' => $vm->id,
            'device_number' => $vbdParams['userdevice'],
            'is_cdrom' => $vbdParams['type'] === 'CD',
            'hypervisor_uuid' => $vbdParams['vdi-uuid'],
            'hypervisor_data' => $disk,
            'iam_account_id' => $vm->iam_account_id,
            'iam_user_id' => $vm->iam_user_id,
            'is_draft' => false,
        ];

        $config->update($data);
    }

    public function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        //  Checking if the virtual machine actually has a VIF. If has we are syncing those vifs.
        $vifs = VirtualMachinesXenService::getVifs($vm);

        $syncedVifs = [];

        foreach ($vifs as $vif) {
            if (!count($vif)) {
                continue;
            }

            foreach ($netConfig as $config) {
                if ($config->device_number == $vif['device']) {
                    $this->syncVifConfig($vm, $vif, $config);

                    $syncedVifs[] = $vif['uuid'];
                }
            }
        }

        foreach ($vifs as $vif) {
            if (!count($vif)) {
                continue;
            }

            if (!in_array($vif['uuid'], $syncedVifs)) {
                VirtualMachinesXenService::destroyVif($vm, $vif['uuid']);
            }
        }

        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        foreach ($netConfig as $config) {
            //  Here we check if the VIF not exists. If not exists hypervisor_uuid is null
            if ($config->hypervisor_uuid == null) {
                (new Attach($config))->handle();
            }
        }
    }

    private function syncVifConfig(VirtualMachines $vm, $vif, $config): void
    {
        $params = VirtualMachinesXenService::getVifParams($vm, $vif['uuid']);

        $vif = $params[0];

        $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('network_uuid', $vif['network-uuid'])
            ->first();

        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('vlan', $cmni->vlan)
            ->first();

        $config->update([
            'hypervisor_uuid'   => $vif['uuid'],
            'hypervisor_data'   => $vif,
            'mac_addr'          => $vif['MAC'],
            'iaas_network_id'   =>  $network ? $network->id : null,
            'bandwitdh_limit'   =>  -1,
            'is_draft'          =>  false
        ]);
    }

    // -- BackupCapableInterface ---------------------------------------------------------

    public function mountDefaultBackupRepository(ComputeMembers $computeMember): array
    {
        return ComputeMemberXenService::mountDefaultBackupRepository($computeMember);
    }

    public function stripAllNetworkCards(VirtualMachines $vm): void
    {
        foreach (VirtualMachinesXenService::getVifs($vm) as $vif) {
            if (!empty($vif['uuid'])) {
                VirtualMachinesXenService::destroyVif($vm, $vif['uuid']);
            }
        }
    }

    public function exportToDefaultBackupRepository(VirtualMachines $vm): array
    {
        return VirtualMachinesXenService::exportToDefaultBackupRepository($vm);
    }

    public function takeSnapshotRaw(VirtualMachines $vm): array
    {
        return VirtualMachinesXenService::takeSnapshot($vm);
    }

    public function fixVmName(VirtualMachines $vm): bool
    {
        return VirtualMachinesXenService::fixName($vm);
    }

    public function cloneVmRaw(VirtualMachines $vm): array
    {
        return VirtualMachinesXenService::cloneVm($vm);
    }

    public function mountBackupRepository(ComputeMembers $computeMember, Repositories $repository): array
    {
        return ComputeMemberXenService::mountRepository($computeMember, $repository);
    }

    public function isBackupRunning(ComputeMembers $computeMember, string $vmName): ?float
    {
        return VirtualMachinesXenService::isBackupRunning($computeMember, $vmName);
    }

    public function exportToRepositoryInBackground(VirtualMachines $vm, Repositories $repository, string $exportName, VirtualMachineBackups $vmBackup): bool
    {
        return VirtualMachinesXenService::exportToRepositoryInBackground(
            vm: $vm,
            repositories: $repository,
            exportName: $exportName,
            vmBackup: $vmBackup,
        );
    }
}
