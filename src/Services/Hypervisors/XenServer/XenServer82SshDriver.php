<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use NextDeveloper\IAAS\Contracts\CloneCapableInterface;
use NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface;
use NextDeveloper\IAAS\Contracts\ConsoleCapableInterface;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Contracts\EventTranslatorCapableInterface;
use NextDeveloper\IAAS\Contracts\ExportCapableInterface;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Contracts\NetworkCapableInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Contracts\SnapshotCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\HypervisorService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Events\XenServerEventTranslator;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\ValueObjects\ConsoleSession;
use NextDeveloper\IAAS\ValueObjects\NormalizedHypervisorEvent;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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
    ConfigurationIsoCapableInterface
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

        return $this->sync($vm);
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
        return ComputeMemberXenService::updateVirtualMachines($computeMember);
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
}
