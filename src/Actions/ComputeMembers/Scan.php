<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\NetworkMemberXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action will scan compute member and sync all findings. Its the same as initiate but without the auto discovery.
 */
class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanned:NextDeveloper\IAAS\ComputeMembers'
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        UserHelper::setAdminAsCurrentUser();

        $this->model = $computeMember;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        if($this->shouldRunCheckpoint(10)) {
            ComputeMemberXenService::updateMemberInformation($this->model);
            $this->setProgress(10, 'Updating compute member information');
        }

        if($this->shouldRunCheckpoint(15)) {
            ComputeMemberXenService::removeDeletedVlans($this->model);
            $this->setProgress(15, 'Removing vlans which are deleted from compute member');
        }

        if($this->shouldRunCheckpoint(20)) {
            ComputeMemberXenService::updateInterfaceInformation($this->model);
            $this->setProgress(20, 'Updating compute member network interface information');
        }

        if($this->shouldRunCheckpoint(30)) {
            ComputeMemberXenService::updateMissingVlans($this->model);
            $this->setProgress(30, 'Updating compute member storage repository information');
        }

        if($this->shouldRunCheckpoint(40)) {
            ComputeMemberXenService::updateNetworkInformation($this->model);
            $this->setProgress(40, 'Updating compute member bridges/networks information');
        }

        if($this->shouldRunCheckpoint(60)) {
            ComputeMemberXenService::updateStorageVolumes($this->model);
            $this->setProgress(60, 'Updating compute member storage volume information');
        }

        if($this->shouldRunCheckpoint(65)) {
            (new \NextDeveloper\IAAS\Actions\ComputeMembers\ScanVirtualMachines($this->model))->handle();
            $this->setProgress(65, 'Updating VMs in compute member:');
        }

        if($this->shouldRunCheckpoint(70)) {
            ComputeMemberXenService::updateMemberInformation($this->model);
            $this->setProgress(70, 'Updating compute member resources');
        }

        if($this->shouldRunCheckpoint(80)) {
            ComputeMemberXenService::updateConnectionInformation($this->model);
            $this->setProgress(80, 'Updating network information');
        }

        if($this->shouldRunCheckpoint(90)) {
            NetworkMemberXenService::createNetworkMemberFromComputeMember($this->model);
            $this->setProgress(90, 'Creating network member');
        }

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member scanned and synced');
    }
}
