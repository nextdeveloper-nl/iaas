<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\Hypervisors\HypervisorService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\NetworkMemberXenService;

/**
 * This action initiates compute members by creating the necessary resources such as Compute, Storage, and Network.
 * In addition to that it will start auto discovery if the user is asked for it.
 */
class UpdateResources extends AbstractAction
{

    public const EVENTS = [
        'updating:NextDeveloper\IAAS\ComputeMembers',
        'updated:NextDeveloper\IAAS\ComputeMembers',
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        $this->model = $computeMember;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Updating compute member resources started');

        Events::fire('updating:NextDeveloper\IAAS\ComputeMembers', $this->model);

        ComputeMemberXenService::updateMemberInformation($this->model);

        Events::fire('updated:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Updating compute member resources finished');
    }
}
