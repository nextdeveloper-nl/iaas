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
class Initiate extends AbstractAction
{

    public const EVENTS = [
        'initiated:NextDeveloper\IAAS\ComputeMembers',
        'created:NextDeveloper\IAAS\StorageMembers',
        'created:NextDeveloper\IAAS\NetworkMembers',
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        $this->model = $computeMember;

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $hypervisorModel = HypervisorService::getHypervisor($this->model);

        $this->model->update([
            'hypervisor_model'  =>  $hypervisorModel
        ]);

        $this->model = $this->model->fresh();

        switch($this->model->hypervisor_model) {
            case 'XenServer 8.2':
                $this->initiateXenServer();
                break;
        }

        Events::fire('initiated:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member initiated');
    }

    private function initiateXenserver()
    {
        /**
         * - We will create network member with the exact name as the compute member and the same for storage member.
         * - We will copy the compute member's configuration to the network and storage members.
         * - - we will copy ssh username, ssh password, ssh port.
         * - - The network member and storage member will have the same IP address as the compute member.
         * - - The network and storage member has their own network pool id and storage pool id. You can find them
         * the relation with cloud node.
         *
         */

        $this->setProgress(0, 'Updating compute member information');
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

        $this->setFinished('Compute member initiated');
    }
}
