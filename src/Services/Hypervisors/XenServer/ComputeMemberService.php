<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

class ComputeMemberService
{
    public static function sync(ComputeMembers $computeMember) : ComputeMembers
    {
        Log::info('[ComputeMemberService@sync] Checking if we can connect to: ' . $computeMember->name);

        $command = 'hostname';
        $hostname = self::performCommand($command, $computeMember);
        $hostname = $hostname[0]['output'];

        $command = 'uptime';
        $uptime = self::performCommand($command, $computeMember);
        $uptime = $uptime[0]['output'];

        $command = 'xe host-list';
        $hostlist = self::performCommand($command, $computeMember);
        $hostListArray = ParserService::parseListResult($hostlist[0]['output']);

        $hypervisor = null;

        foreach ($hostListArray as $host) {
            $command = 'xe host-param-list uuid=' . $host['uuid'];
            $hostInfo = self::performCommand($command, $computeMember);
            $hostInfo = ParserService::parseResult($hostInfo[0]['output']);

            //  We are checking this because this host can be a part of a pool and we need to get the correct host
            if($hostInfo['hostname'] == $hostname) {
                $hypervisor = $hostInfo;
                break;
            }
        }

        Log::info('[ComputeMemberService@sync] We got the correct host: ' . $hypervisor['name-label']);
        Log::info('[ComputeMemberService@sync] Going to update compute member information');

        $computeMember->update([
            'name'  => $hypervisor['name-label'],
            'hostname'  =>  $hypervisor['hostname'],
            'hypervisor_uuid'   =>  $hypervisor['uuid'],
            'uptime'            =>  $uptime,
            'overbooking_ratio' => $computeMember->overbooking_ratio == 0 ? 15 : $computeMember->overbooking_ratio,
        ]);

        return $computeMember->fresh();
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
