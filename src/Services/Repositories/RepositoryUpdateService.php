<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use NextDeveloper\IAAS\Database\Models\Repositories;

class RepositoryUpdateService
{
    public static function updateInformation(Repositories $repo) : Repositories
    {
        $command = 'hostname -I | awk \'{print $1}\''; // get the ip address of the server
        $ipAddr = self::performCommand($command, $repo);
        $ipAddr = $ipAddr[0]['output'];

        $repo->update([
            'local_ip_addr' =>  $ipAddr,
            'is_behind_firewall'    =>  $ipAddr != $repo->ip_addr ? true : false
        ]);

        return $repo->fresh();
    }

    public static function performCommand($command, Repositories $repo) : ?array
    {
        if($repo->is_management_agent_available == true) {
            return $repo->performAgentCommand($command);
        } else {
            return $repo->performSSHCommand($command);
        }
    }
}
