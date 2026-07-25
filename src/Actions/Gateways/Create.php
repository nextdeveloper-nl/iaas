<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\GatewaysService;

/**
 * Explicit, user-triggerable gateway provisioning - dispatched via
 * POST /iaas/networks/{ref}/do/provision-gateway for a Network that either isn't on a
 * firewall-enabled cloud node (so the implicit Actions\Networks\Create flow skipped it)
 * or didn't get a gateway for some other reason. Shares its actual provisioning logic
 * with that implicit flow via GatewaysService::provisionForNetwork() - see
 * Actions/Networks/Create.php.
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\Gateways',
        'created:NextDeveloper\IAAS\Gateways',
        'create-failed:NextDeveloper\IAAS\Gateways'
    ];

    public function __construct(Networks $network, $params = null, $previousAction = null)
    {
        $this->model = $network;

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Provisioning gateway');

        $gatewayType = is_array($this->params) ? ($this->params['gateway_type'] ?? null) : null;

        try {
            $gateway = GatewaysService::provisionForNetwork($this->model, [
                'gateway_type' => $gatewayType,
            ]);
        } catch (\Throwable $e) {
            CommentsService::createSystemComment(
                'We could not provision a gateway for this network: ' . $e->getMessage(),
                $this->model
            );

            $this->setFinishedWithError('Gateway provisioning failed: ' . $e->getMessage());
            return;
        }

        if (!$gateway) {
            $this->setFinished('Cannot provision a gateway for this network - its cloud node is not firewall-enabled and no gateway_type was specified.');
            return;
        }

        $this->model->update([
            'iaas_gateway_id' => $gateway->id,
        ]);

        $this->setProgress(100, 'Gateway provisioned');
    }
}
