<?php

namespace NextDeveloper\IAAS\Actions\Networks;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\NetworksService;

/**
 * Previously an unimplemented trigger_error() stub that also took the wrong model type
 * (VirtualMachines instead of Networks) - meaning deleting a Network with a gateway left
 * its firewall VM and Gateways row permanently orphaned. Fixed to tear down the gateway
 * first, then the network record itself.
 *
 * Note: this does not yet remove the network's VLAN config from physical switch members
 * (see Actions/Networks/Create.php's DellS6100::addNetworkToSwitch) - no removal
 * counterpart exists anywhere in this codebase today, so that remains a separate,
 * pre-existing gap outside this fix's scope.
 */
class Delete extends AbstractAction
{
    public const EVENTS = [
        'deleting:NextDeveloper\IAAS\Networks',
        'deleted:NextDeveloper\IAAS\Networks',
        'delete-failed:NextDeveloper\IAAS\Networks'
    ];

    public function __construct(Networks $network, $params = null, $previousAction = null)
    {
        $this->model = $network;

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Deleting network');

        if ($this->model->iaas_gateway_id) {
            $gateway = Gateways::find($this->model->iaas_gateway_id);

            if ($gateway) {
                dispatch(new \NextDeveloper\IAAS\Actions\Gateways\Delete($gateway));
            }
        }

        NetworksService::delete($this->model->uuid);

        $this->setProgress(100, 'Network deleted');
    }
}
