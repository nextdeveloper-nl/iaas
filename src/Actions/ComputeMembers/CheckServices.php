<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\NetworkMemberXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action will scan compute member and sync all findings. Its the same as initiate but without the auto discovery.
 */
class CheckServices extends AbstractAction
{
    private $reDeploy = false;

    public const EVENTS = [
        'checked:NextDeveloper\IAAS\ComputeMembers'
    ];

    public function __construct(ComputeMembers $computeMember, $params = null, $previous = null)
    {
        $this->model = $computeMember;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        UserHelper::setAdminAsCurrentUser();

        $this->setProgress(0, 'Checking running PlusClouds services on compute member');

        $isEventsServiceRunning = ComputeMembersService::checkEventsService($this->model);

        $isRrdServiceRunning = ComputeMembersService::checkRrdService($this->model, $this->reDeploy);

        $isIpmiServiceRunning = ComputeMembersService::checkIpmiService($this->model, $this->reDeploy);

        Events::fire('checked:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member services are checked');
    }
}
