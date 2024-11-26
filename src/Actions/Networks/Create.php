<?php

namespace NextDeveloper\IAAS\Actions\Networks;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\NetworksService;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use PharIo\Manifest\Author;

/**
 * This action converts the virtual machine into a template
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\Networks',
        'created:NextDeveloper\IAAS\Networks',
        'create-failed:NextDeveloper\IAAS\Networks'
    ];

    public function __construct(Networks $network)
    {
        $this->model = $network;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiating network');

        $networkMembers = NetworkMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_network_pool_id', $this->model->iaas_network_pool_id)
            ->get();

        foreach ($networkMembers as $member) {
            switch ($member->switch_type) {
                case 'dells6100':
                    DellS6100::addNetworkToSwitch($this->model, $member);
                    break;
                case 'ovs':
                default:
                    //  Do nothing
            }
        }

        $this->setProgress(100, 'Network initiated');
    }
}
