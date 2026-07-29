<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\GatewaysService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Explicit, user-triggerable gateway provisioning - dispatched via
 * POST /iaas/networks/{ref}/do/provision-gateway for a Network that didn't get a
 * gateway from the implicit Actions\Networks\Create flow (e.g. it was created with
 * create_gateway: false), didn't get one for some other reason, or had one deleted and
 * is getting a replacement. Shares its actual provisioning logic with that implicit flow
 * via GatewaysService::provisionForNetwork() - see Actions/Networks/Create.php. Accepts
 * an optional gateway_type and/or iaas_repository_image_id in the request body to pin the
 * new gateway to a specific driver/firewall image instead of the deployment default.
 * Refuses to run (see the guard at the top of handle()) if the network already has a
 * live gateway - delete it first via DELETE /iaas/gateways/{id}. If the referenced
 * gateway or its underlying VM turns out to be gone/soft-deleted (a stale
 * iaas_gateway_id left over from an unlinking write that silently no-op'd - see the
 * guard's own comments), it self-heals by clearing the FK and provisioning normally,
 * rather than refusing forever.
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

        $this->queue = 'iaas';

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        //  Refuse to provision a second gateway for a network that already has one -
        //  unless what's actually there is stale. Both old and new firewalls' LAN NIC
        //  want the same hardcoded 10.128.0.1/32 (see GatewaysService::provisionForNetwork()),
        //  and IpAddressesService::create() has no uniqueness check, so a genuinely live
        //  second gateway would risk a duplicate/conflicting IP and an orphaned VM.
        //  Re-fetch fresh since this action may run some time after the network was
        //  first loaded.
        $network = $this->model->fresh();

        if ($network->iaas_gateway_id) {
            //  iaas_gateway_id being set doesn't guarantee the gateway is actually alive.
            //  Actions\Gateways\Delete's own network-unlinking write can silently no-op if
            //  it runs somewhere AuthorizationScope can't resolve a role for the current
            //  context (falls back to AnonymousRole, which requires is_public=true - every
            //  VDC network is created is_public=false), leaving this FK stuck pointing at a
            //  gateway/VM that's already gone. Bypass the same scopes here for the same
            //  reason - these are internal consistency checks, not user-facing reads - and
            //  use withTrashed() so a soft-deleted row reads back as "gone", not "missing
            //  because scoped out", which would otherwise look identical to "alive".
            $existingGateway = Gateways::withoutGlobalScope(AuthorizationScope::class)
                ->withoutGlobalScope(LimitScope::class)
                ->withTrashed()
                ->find($network->iaas_gateway_id);

            $existingVm = $existingGateway
                ? VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                    ->withoutGlobalScope(LimitScope::class)
                    ->withTrashed()
                    ->find($existingGateway->iaas_virtual_machine_id)
                : null;

            $gatewayIsAlive = $existingGateway && !$existingGateway->trashed()
                && $existingVm && !$existingVm->trashed() && !$existingVm->is_lost;

            if ($gatewayIsAlive) {
                CommentsService::createSystemComment(
                    'This network already has a gateway; delete it first (DELETE /iaas/gateways/{id}) ' .
                    'before provisioning a new one.',
                    $this->model
                );

                $this->setFinishedWithError('This network already has a gateway.');
                return;
            }

            //  Stale reference - self-heal instead of refusing forever. An instance
            //  ->update() (rather than a Networks::where(...) query) is used deliberately:
            //  it matches by primary key via newQueryWithoutScopes() internally, so it
            //  isn't subject to the same AuthorizationScope gap this whole check exists
            //  to work around.
            $network->update(['iaas_gateway_id' => null]);
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
