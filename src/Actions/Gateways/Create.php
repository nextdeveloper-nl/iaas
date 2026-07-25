<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\GatewaysService;

/**
 * Explicit, user-triggerable gateway provisioning - dispatched via
 * POST /iaas/networks/{ref}/do/provision-gateway for a Network that didn't get a
 * gateway from the implicit Actions\Networks\Create flow (e.g. it was created with
 * create_gateway: false) or didn't get one for some other reason. Shares its actual
 * provisioning logic with that implicit flow via GatewaysService::provisionForNetwork()
 * - see Actions/Networks/Create.php.
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

        //  Dispatched through the generic AbstractNetworksService::doAction(), which
        //  passes the whole request body as a single element of the variadic ...$params
        //  it collects (NetworksController::doAction() calls
        //  NetworksService::doAction($objectId, $action, request()->all())) - so
        //  $this->params here arrives as [0 => [...request body...]], not the request
        //  body directly. AbstractAction's own constructor only unwraps that when
        //  static::PARAMS is defined (it isn't, here), so we unwrap it ourselves the
        //  same way, while still tolerating a direct, unwrapped array for callers that
        //  construct this action themselves instead of going through doAction().
        $params = $this->params;

        if (is_array($params) && array_key_exists(0, $params) && is_array($params[0])) {
            $params = $params[0];
        }

        $gatewayType = is_array($params) ? ($params['gateway_type'] ?? null) : null;

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
            $this->setFinished('Cannot provision a gateway for this network.');
            return;
        }

        $this->model->update([
            'iaas_gateway_id' => $gateway->id,
        ]);

        $this->setProgress(100, 'Gateway provisioned');
    }
}
