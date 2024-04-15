<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

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
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        /**
         * - We will create network member with the exact name as the compute member and the same for storage member.
         * - We will copy the compute member's configuration to the network and storage members.
         * - - we will copy ssh username, ssh password, ssh port.
         * - - The network member and storage member will have the same IP address as the compute member.
         * - - The network and storage member has their own network pool id and storage pool id. You can find them
         * the relation with cloud node.
         *
         */

        $this->setProgress(10, 'Auto discovering compute member features');
        $this->autoDiscoverComputeMemberFeatures();

        $this->setProgress(20, 'Auto discovering the devices');
        $this->autoDiscoverDevices();

        $this->setProgress(30, 'Auto discovering network interfaces');
        $this->autoDiscoverNetworkInterfaces();

        $this->setProgress(40, 'Creating storage member');
        $this->createStorageMember();

        $this->setProgress(50, 'Creating network member');
        $this->createNetworkMember();

        $this->setProgress(60, 'Discovering network for other members');
        $this->discoverNetwork();

        Events::fire('initiated:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member initiated');
    }

    private function createNetworkMember()
    {
        // Create network member
    }

    private function createStorageMember()
    {
        // Create storage member
    }

    private function autoDiscoverComputeMemberFeatures()
    {
        // Auto discover compute member features
    }

    private function autoDiscoverDevices()
    {
        // Auto discover devices
        //  We will look for lspci and lsusb commands to understand the devices.
    }

    private function autoDiscoverNetworkInterfaces()
    {
        // Auto discover network interfaces
    }

    private function discoverNetwork()
    {
        //  Here we will discover the network and try to understand what we have missing in the cloud node.
        //  If we can login with the same ssh credentials, we will try to understand the machine.
        //  If the machine is a compute member, we will create a new compute member.
    }
}
