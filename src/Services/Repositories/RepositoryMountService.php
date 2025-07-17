<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;

class RepositoryMountService
{
    public static function mountRepositoryToComputeMember(Repositories $repo, ComputeMembers $computeMember)
    {

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
