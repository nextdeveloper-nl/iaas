<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\NetworkMemberXenService;

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
        $this->model = $computeMember;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $this->setProgress(10, 'Updating compute member information');
        ComputeMemberXenService::updateMemberInformation($this->model);

        $this->setProgress(20, 'Updating compute member network interface information');
        ComputeMemberXenService::updateInterfaceInformation($this->model);

        $this->setProgress(40, 'Updating compute member bridges/networks information');
        ComputeMemberXenService::updateNetworkInformation($this->model);

        $this->setProgress(60, 'Updating compute member storage volume information');
        ComputeMemberXenService::updateStorageVolumes($this->model);

        $this->setProgress(80, 'Updating network information');
        ComputeMemberXenService::updateConnectionInformation($this->model);

        $this->setProgress(90, 'Creating network member');
        NetworkMemberXenService::createNetworkMemberFromComputeMember($this->model);

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member scanned and synced');
    }
}
