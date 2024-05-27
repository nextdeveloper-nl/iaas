<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

class VirtualMachinesXenService extends AbstractXenService
{
    public static function getVmParametersByUuid(ComputeMembers $computeMember, $vmUuid) : array
    {
        if(config('leo.debug.iaas.compupe_members'))
            Log::error('[ComputeMembersXenService@mountVmRepo] I am taking the' .
                ' parameters of the VM (' . $vm->name. '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-param-list uuid=' . $vmUuid;
        $result = self::performCommand($computeMember, $command);

        dd($result);

        return self::parseResult($result[0]['output']);
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
