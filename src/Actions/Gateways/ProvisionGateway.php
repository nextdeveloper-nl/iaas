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
 * create_gateway: false), didn't get one for some other reason, or had one deleted and
 * is getting a replacement. Shares its actual provisioning logic with that implicit flow
 * via GatewaysService::provisionForNetwork() - see Actions/Networks/Create.php. Accepts
 * an optional gateway_type and/or iaas_repository_image_id in the request body to pin the
 * new gateway to a specific driver/firewall image instead of the deployment default.
 * Refuses to run (see the guard at the top of handle()) if the network already has one -
 * delete it first via DELETE /iaas/gateways/{id}.
 */
class ProvisionGateway extends AbstractAction
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
        //  Refuse to provision a second gateway for a network that already has one.
        //  Without this, a client double-click/retry, or racing the async teardown of
        //  DELETE /iaas/gateways/{id} (Actions\Gateways\Delete - queued, so only
        //  synchronous under QUEUE_CONNECTION=sync), could dispatch this while the old
        //  gateway/VM is still mid-teardown. Both old and new firewalls' LAN NIC get the
        //  same hardcoded 10.128.0.1/32 (see GatewaysService::provisionForNetwork()), and
        //  IpAddressesService::create() has no uniqueness check - a second concurrent
        //  provision risks a duplicate/conflicting IP row and an orphaned VM. Re-fetch
        //  fresh since this action may run some time after the network was first loaded.
        if ($this->model->fresh()->iaas_gateway_id) {
            CommentsService::createSystemComment(
                'This network already has a gateway; delete it first (DELETE /iaas/gateways/{id}) ' .
                'before provisioning a new one.',
                $this->model
            );

            $this->setFinishedWithError('This network already has a gateway.');
            return;
        }

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

        $overrides = [
            'gateway_type' => $gatewayType,
        ];

        //  Public field name matches the VDC-creation wizard's iaas_repository_image_id
        //  and every other iaas_*_id FK-style field in this domain, translated here to the
        //  bare repository_image_id key GatewaysService::provisionForNetwork() expects.
        if (is_array($params) && !empty($params['iaas_repository_image_id'])) {
            $overrides['repository_image_id'] = $params['iaas_repository_image_id'];
        }

        try {
            $gateway = GatewaysService::provisionForNetwork($this->model, $overrides);
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
