<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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
