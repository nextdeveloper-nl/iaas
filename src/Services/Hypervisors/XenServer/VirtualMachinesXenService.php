<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class VirtualMachinesXenService extends AbstractXenService
{
    public static function start(VirtualMachines $vm) : VirtualMachines
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@start] I am starting the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-start uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return $vm;
    }

    public static function restart(VirtualMachines $vm) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@restart] I am restarting the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-reboot uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function unpause(VirtualMachines $vm) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@pause] I am unpausing the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-unpause uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function pause(VirtualMachines $vm) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@pause] I am pausing the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-pause uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function forceRestart(VirtualMachines $vm) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@restart] I am restarting the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-reboot force=true uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function shutdown(VirtualMachines $vm) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@shutdown] I am shutting down the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-shutdown uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function forceShutdown(VirtualMachines $vm) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@shutdown] I am shutting down the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-shutdown force=true uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function getVmParameters(VirtualMachines $vm) : array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmParameters] I am taking the' .
                ' parameters of the VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-param-list uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function getVmParametersByUuid(ComputeMembers $computeMember, $vmUuid) : array
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmParametersByUuid] I am taking the' .
                ' parameters of the VM (' . $vmUuid. ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-param-list uuid=' . $vmUuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function getVmDisks(VirtualMachines $vm) : array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmDisks] I am taking the' .
                ' disks of the VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vbd-list vm-uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $list = self::parseListResult($result[0]['output']);

        return $list;
    }

    /**
     * Setting the CPU for this Virtual Machine
     *
     * @param VirtualMachines $vm
     * @param int $coreCount Core count for this VM like 16 cores.
     * @param int $corePerSocket If not given we will distribute cores evenly
     * @return VirtualMachines
     */
    public static function setCPUCore(VirtualMachines $vm, $coreCount, $corePerSocket = null) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@setCPUCore] I am updating the' .
                ' CPU of the VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        //  Setting vCPU max
        $command = 'xe vm-param-set VCPUs-max=' . $coreCount . ' uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        //  Setting vCPU on boot
        $command = 'xe vm-param-set VCPUs-at-startup=' . $coreCount . ' uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        if ($corePerSocket) {
            $corePerSocket = (int)$corePerSocket;
            //  Right now we are assuming that there is 2 CPUs only. We can change this later
            //  @todo: change this later to dynamic
            //  $corePerSocket = $coreCount / 2;

            $command = 'xe vm-param-set platform:cores-per-socket=' . $coreCount . ' uuid=' . $vm->hypervisor_uuid;
            $result = self::performCommand($command, $computeMember);
            $result = $result[0]['output'];
        }

        return true;
    }

    /**
     * Sets the ram
     *
     * @param VirtualMachines $vm
     * @param int $ram MB of ram requestes
     * @return VirtualMachines
     */
    public static function setRam(VirtualMachines $vm, $ram) : bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@setRam] I am updating the' .
                ' CPU of the VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        //  Converting GB to Bytes
        $ramBytes = $ram * 1024 * 1024 * 1024;

        //  Setting RAM
        $command = 'xe vm-memory-limits-set static-min=' . $ramBytes;
        $command .= ' dynamic-min=' . $ramBytes;
        $command .= ' dynamic-max=' . $ramBytes;
        $command .= ' static-max=' . $ramBytes;
        $command .= ' uuid=' . $vm->hypervisor_uuid;

        if(config('leo.debug.iaas.compute_members'))
            logger()->info('[VirtualMachineService@setRam] Executing command: ' . $command);

        $result = self::performCommand($command, $computeMember);
        $result = $result[0]['output'];

        return true;
    }

    public static function performCommand($command, ComputeMembers $computeMember) : ?array
    {
        if($computeMember->is_management_agent_available == true) {
            return $computeMember->performAgentCommand($command);
        } else {
            return $computeMember->performSSHCommand($command);
        }
    }
}
