<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Services\Hypervisors\GatewayDriverManager;

/**
 * Pushes the gateway's gateway_data desired-state config (rules/NAT) to the live
 * appliance via its driver - distinct from Actions\VirtualMachines\Commit, which stays
 * the hypervisor-provisioning engine and is unrelated to this. Reserved for bulk
 * resync/drift-repair (e.g. after a direct PATCH to gateway_data); individual rule/NAT
 * CRUD through GatewaysService calls the driver synchronously and inline instead, for
 * immediate success/failure feedback on each change.
 *
 * Previously an unimplemented trigger_error() stub.
 */
class Commit extends AbstractAction
{
    public const EVENTS = [
        'committing:NextDeveloper\IAAS\Gateways',
        'committed:NextDeveloper\IAAS\Gateways',
        'commit-failed:NextDeveloper\IAAS\Gateways'
    ];

    public function __construct(Gateways $gateway, $params = null, $previousAction = null)
    {
        $this->model = $gateway;

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Applying gateway configuration');

        $driver = app(GatewayDriverManager::class)->requireAdapter($this->model);
        $driver->applyConfiguration($this->model);

        $this->setProgress(100, 'Gateway configuration applied');
    }
}
