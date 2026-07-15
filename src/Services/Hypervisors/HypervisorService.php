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

        if ($result['dry_run'] ?? false) {
            //  Nothing actually ran, so there's no real lsb_release output to detect a
            //  version from - report a clearly-labeled placeholder instead of the
            //  misleading 'Other' this check would otherwise fall through to.
            return 'XenServer 8.2 (dry-run, undetected)';
        }

        if(Str::contains($result['output'], 'Citrix Hypervisor release 8.2.0')) {
            return 'XenServer 8.2';
        }

        return 'Other';
    }
}
