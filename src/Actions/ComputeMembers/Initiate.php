<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\NetworkMemberXenService;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;

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

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $driver = app(VirtualMachineManager::class)->getAdapterForComputeMember($this->model);

        if ($driver instanceof HostSyncInterface) {
            $this->model->update([
                'hypervisor_model'  =>  $driver->detectVersion($this->model),
            ]);

            $this->model = $this->model->fresh();

            $this->initiateWithDriver($driver);
        }

        Events::fire('initiated:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member initiated');
    }

    /**
     * - We will create network member with the exact name as the compute member and the same for storage member.
     * - We will copy the compute member's configuration to the network and storage members.
     * - - we will copy ssh username, ssh password, ssh port.
     * - - The network member and storage member will have the same IP address as the compute member.
     * - - The network and storage member has their own network pool id and storage pool id. You can find them
     * the relation with cloud node.
     */
    private function initiateWithDriver(HostSyncInterface $driver)
    {
        $this->setProgress(0, 'Updating compute member information');
        $this->model = $driver->syncMember($this->model);

        $this->setProgress(20, 'Updating compute member network interface information');
        $this->model = $driver->syncInterfaces($this->model);

        $this->setProgress(40, 'Updating compute member bridges/networks information');
        $this->model = $driver->syncNetworks($this->model);

        $this->setProgress(60, 'Updating compute member storage volume information');
        $this->model = $driver->syncStorageVolumes($this->model);

        //  Not routed through VirtualMachineManager: connection-info sync and network-member
        //  mirroring have no capability interface yet - see docs/hypervisor-driver-architecture.md.
        $this->setProgress(80, 'Updating network information');
        ComputeMemberXenService::updateConnectionInformation($this->model);

        $this->setProgress(90, 'Creating network member');
        NetworkMemberXenService::createNetworkMemberFromComputeMember($this->model);

        $this->setFinished('Compute member initiated');
    }
}
