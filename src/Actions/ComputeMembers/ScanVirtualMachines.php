<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;

/**
 * This action will scan compute member and sync all findings
 */
class ScanVirtualMachines extends AbstractAction
{
    public const EVENTS = [
        'scanned:NextDeveloper\IAAS\ComputeMembers'
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        $this->model = $computeMember;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $virtualMachines = ComputeMembersService::getVirtualMachines($this->model);

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member scanned and synced');
    }
}
