<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use NextDeveloper\IAAS\Contracts\CloneCapableInterface;
use NextDeveloper\IAAS\Contracts\ConsoleCapableInterface;
use NextDeveloper\IAAS\Contracts\EventTranslatorCapableInterface;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Contracts\SnapshotCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\AgentResultMapper;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\ComputeMemberAgentSyncService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\VirtualMachineAgentSyncService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentClient;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Events\XenServerEventTranslator;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\ValueObjects\ConsoleSession;
use NextDeveloper\IAAS\ValueObjects\NormalizedHypervisorEvent;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Hypervisor driver that talks to the xenserver HTTPS agent (see Agent/XenAgentClient) instead
 * of SSH + `xe`. Registered for "xenserver-8.2-agent" and "xcp-ng-8.2-agent" only, so every
 * existing pool keeps using XenServer82SshDriver until its ComputePools.virtualization is
 * changed on purpose.
 *
 * Rules of this driver:
 *  - There is no fallback to SSH. If the agent is unreachable, the operation is rejected or the
 *    host has not been opted in (management_data.http_agent.enabled), a XenAgentException is
 *    thrown and the caller sees the reason.
 *  - It does not touch XenServer82SshDriver or the *XenService classes.
 *  - Only the capabilities implemented below are declared. Callers check capabilities with
 *    instanceof, so anything not yet declared (disk, network card, host sync, provisioning,
 *    export, backup) still goes through their existing code path until a later phase adds it.
 *
 * Phase 2: VM lifecycle, snapshot, clone, resize, console, event translation.
 * Phase 4a: host sync (what Initiate runs) - member, interfaces, networks, storage volumes, via
 * Agent/ComputeMemberAgentSyncService. Phase 4b: VM-level sync (VM rows, disks, network cards) and
 * per-volume disk sync via Agent/VirtualMachineAgentSyncService.
 */
class XenServer82AgentDriver implements
    VirtualMachineAdapterInterface,
    SnapshotCapableInterface,
    CloneCapableInterface,
    ResizeCapableInterface,
    ConsoleCapableInterface,
    EventTranslatorCapableInterface,
    HostSyncInterface
{
    public function __construct(private readonly array $config = [])
    {
    }

    // -- VirtualMachineAdapterInterface ----------------------------------------------

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $this->command($vm, 'xenserver.vm.start');

        return $this->sync($vm);
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->command($vm, $force ? 'xenserver.vm.force_shutdown' : 'xenserver.vm.shutdown');

        //  Same reasoning as the SSH driver: a clean shutdown can return before the guest has
        //  finished powering off, so poll briefly instead of trusting one immediate sync().
        return $this->syncUntilPowerState($vm, 'halted');
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->command($vm, $force ? 'xenserver.vm.force_reboot' : 'xenserver.vm.reboot');

        return $this->sync($vm);
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        $this->command($vm, 'xenserver.vm.pause');

        return $this->sync($vm);
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        //  XenServer has two different "resume" meanings: unpause (frozen in memory) and resume
        //  (suspended to disk). The SSH driver only knows unpause. Here the VM's current
        //  power-state decides, read live from the agent, not from the cached DB status.
        $state = $this->getHypervisorData($vm)['power-state'] ?? null;

        $this->command($vm, $state === 'suspended' ? 'xenserver.vm.resume' : 'xenserver.vm.unpause');

        return $this->sync($vm);
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        //  The SSH driver throws here (no suspend in VirtualMachinesXenService). The agent has
        //  xenserver.vm.suspend, so this driver supports it.
        $this->command($vm, 'xenserver.vm.suspend');

        return $this->sync($vm);
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        return AgentResultMapper::paramList($this->command($vm, 'xenserver.vm.param_list'));
    }

    public function delete(VirtualMachines $vm): bool
    {
        //  confirm=true is mandatory for the agent. This is the explicit delete path, the only
        //  place it is sent. As with `xe vm-destroy`, the VM's disks stay on the SR.
        $this->command($vm, 'xenserver.vm.destroy', ['confirm' => true]);

        return true;
    }

    public function sync(VirtualMachines $vm): VirtualMachines
    {
        $params = $this->getHypervisorData($vm);

        if (!$params || !array_key_exists('power-state', $params)) {
            //  Same as the SSH driver: always write hypervisor_data, so a caller checking it
            //  after sync() sees THIS call's response and not a stale successful one.
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
        throw new \RuntimeException('listAll() is not scoped in this driver - there is no host/pool-wide ' .
            '"list every vm" call without a compute member. Use HostSyncInterface::syncVirtualMachines() ' .
            'for a given compute member once the agent driver implements it.');
    }

    // -- SnapshotCapableInterface -----------------------------------------------------

    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null): VirtualMachines
    {
        $result = $this->command($vm, 'xenserver.vm.snapshot_create', ['label' => $name]);

        return VirtualMachinesService::create([
            'snapshot_of_virtual_machine'   =>  $vm->id,
            'name'                          =>  $name,
            'hypervisor_uuid'               =>  $this->newUuid($result, 'xenserver.vm.snapshot_create'),
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

        //  The agent takes the snapshot's own hypervisor uuid, not the parent VM's.
        $this->command($snapshot, 'xenserver.vm.snapshot_delete', ['uuid' => $snapshot->hypervisor_uuid], false);

        return true;
    }

    public function restoreSnapshot(VirtualMachines $vm, string $snapshotId): bool
    {
        $snapshot = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $snapshotId)
            ->first();

        if (!$snapshot) {
            return false;
        }

        $this->convertSnapshotToVm($snapshot);

        return true;
    }

    public function listSnapshots(VirtualMachines $vm): array
    {
        //  Same as the SSH driver: snapshots are tracked as VirtualMachines rows.
        return VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('snapshot_of_virtual_machine', $vm->id)
            ->where('is_snapshot', true)
            ->get()
            ->all();
    }

    public function convertSnapshotToVm(VirtualMachines $vm, ?string $name = null): array
    {
        //  $vm is the snapshot row here. snapshot_promote is `snapshot-param-set
        //  is-a-template=false`, the uuid stays the same.
        $params = ['uuid' => $vm->hypervisor_uuid];

        if ($name) {
            $params['new_name'] = $name;
        }

        return $this->command($vm, 'xenserver.vm.snapshot_promote', $params, false);
    }

    // -- CloneCapableInterface ---------------------------------------------------------

    public function clone(VirtualMachines $vm, string $newName, array $options = []): VirtualMachines
    {
        //  The hypervisor-side name-label stays 'cloned-<uuid>' like the SSH driver, the
        //  display name $newName only goes to the DB row. Cloning can be slow on some SRs,
        //  so it runs as an agent background job and waits for it.
        $result = $this->client($vm)->callAsync('xenserver.vm.clone', [
            'uuid'     => $vm->hypervisor_uuid,
            'new_name' => 'cloned-' . $vm->uuid,
        ]);

        return VirtualMachinesService::create([
            'name'                      =>  $newName,
            'hypervisor_uuid'           =>  $this->newUuid($result, 'xenserver.vm.clone'),
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
        $set = [
            'VCPUs-max'         => (string) $cpu,
            'VCPUs-at-startup'  => (string) $cpu,
        ];

        if ($corePerSocket) {
            //  Parity with VirtualMachinesXenService::setCPUCore, which writes the total core
            //  count (not $corePerSocket) into cores-per-socket, i.e. one socket. Kept
            //  identical on purpose so both drivers produce the same topology.
            $set['platform:cores-per-socket'] = (string) $cpu;
        }

        $this->command($vm, 'xenserver.vm.param_set', ['set' => $set]);

        //  Memory (VM must be halted, like the SSH path). All four limits are sent equal: hosts
        //  with dynamic memory control disabled require static_max = dynamic_min = dynamic_max.
        $ramBytes = $ramInMb * 1024 * 1024;

        $this->command($vm, 'xenserver.vm.memory_limits_set', [
            'static_min'  => $ramBytes,
            'static_max'  => $ramBytes,
            'dynamic_min' => $ramBytes,
            'dynamic_max' => $ramBytes,
        ]);

        $vm->update(['cpu' => $cpu, 'ram' => $ramInMb]);

        return $vm->fresh();
    }

    // -- ConsoleCapableInterface ---------------------------------------------------------

    public function getConsoleUrl(VirtualMachines $vm): ConsoleSession
    {
        //  Same implementation as the SSH driver on purpose: the console goes through the
        //  XenServer session-ref console proxy over XML-RPC, which does not use SSH/xe, so it
        //  already works regardless of the management transport.
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

    // -- HostSyncInterface ---------------------------------------------------------------

    public function detectVersion(ComputeMembers $computeMember): string
    {
        $detected = ComputeMemberAgentSyncService::detectVersion($computeMember);

        //  Same product tag as the SSH driver for XCP-ng (config/virtualization.php 'product').
        if (!empty($this->config['product'])) {
            return $this->config['product'] . ' (' . $detected . ')';
        }

        return $detected;
    }

    public function syncMember(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberAgentSyncService::updateMemberInformation($computeMember);
    }

    public function syncInterfaces(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberAgentSyncService::updateInterfaceInformation($computeMember);
    }

    public function syncNetworks(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberAgentSyncService::updateNetworkInformation($computeMember);
    }

    public function syncStorageVolumes(ComputeMembers $computeMember): ComputeMembers
    {
        return ComputeMemberAgentSyncService::updateStorageVolumes($computeMember);
    }

    public function syncVirtualMachines(ComputeMembers $computeMember): ComputeMembers
    {
        return VirtualMachineAgentSyncService::syncVirtualMachines($computeMember);
    }

    public function syncStorageVolumeDisks(ComputeMembers $computeMember, StorageVolumes $volume): StorageVolumes
    {
        return VirtualMachineAgentSyncService::syncStorageVolumeDisks($computeMember, $volume);
    }

    public function isPoolMember(ComputeMembers $computeMember): bool
    {
        return ComputeMemberAgentSyncService::isPoolMember($computeMember);
    }

    // -- internals ---------------------------------------------------------------------

    /**
     * Agent client for the compute member that hosts this VM. Throws XenAgentException (no SSH
     * fallback) when the VM has no compute member.
     */
    protected function client(VirtualMachines $vm): XenAgentClient
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        if (!$computeMember instanceof ComputeMembers) {
            throw new XenAgentException('VM ' . $vm->uuid . ' has no compute member to send the agent command to.');
        }

        return new XenAgentClient($computeMember);
    }

    /**
     * Runs one VM operation. By default the VM is addressed by its hypervisor uuid; pass
     * $addressVm=false when $params already carry the target uuid (snapshot operations).
     */
    private function command(VirtualMachines $vm, string $operation, array $params = [], bool $addressVm = true): array
    {
        if ($addressVm) {
            $params = ['uuid' => $vm->hypervisor_uuid] + $params;
        }

        return $this->client($vm)->call($operation, $params);
    }

    /**
     * The uuid of the object an operation created (snapshot, clone), from output.data.uuid.
     * Fails loudly instead of creating a DB row with an empty hypervisor_uuid.
     */
    private function newUuid(array $result, string $operation): string
    {
        $uuid = $result['data']['uuid'] ?? null;

        if (empty($uuid)) {
            throw new XenAgentException('Agent operation ' . $operation . ' returned no data.uuid.', $operation);
        }

        return $uuid;
    }

    /**
     * Polls sync() until the VM reports the expected power-state or the attempt budget runs
     * out. Non-throwing on a state mismatch: returns the last synced state so the caller's own
     * status check decides what "still not there" means.
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
}
