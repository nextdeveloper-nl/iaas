<?php

namespace NextDeveloper\IAAS\Services\Hypervisors;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

class HypervisorService
{
    public static function getHypervisor(ComputeMembers $computeMember)
    {
        $command = 'lsb_release -a';
        $result = $computeMember->performSSHCommand($command);

        if(Str::contains($result[0]['output'], 'Citrix Hypervisor release 8.2.0')) {
            return 'XenServer 8.2';
        }

        return 'Other';
    }
}
