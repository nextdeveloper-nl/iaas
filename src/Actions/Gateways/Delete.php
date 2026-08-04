<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\GatewaysService;
use NextDeveloper\IAAS\Services\Hypervisors\GatewayDriverManager;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Tears down a gateway: best-effort driver-side cleanup, unlinks any Network pointing at
 * it, deletes the underlying firewall VM (via the existing, fully-implemented
 * Actions\VirtualMachines\Delete, which already handles disks/NICs/IPs/locked-or-lost-VM
 * edge cases), then removes the Gateways row itself. Previously an unimplemented
 * trigger_error() stub with the wrong model type in its constructor.
 */
class Delete extends AbstractAction
{
    public const EVENTS = [
        'deleting:NextDeveloper\IAAS\Gateways',
        'deleted:NextDeveloper\IAAS\Gateways',
        'delete-failed:NextDeveloper\IAAS\Gateways'
    ];

    public function __construct(Gateways $gateway, $params = null, $previousAction = null)
    {
        $this->model = $gateway;

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Deleting gateway');

        $driver = app(GatewayDriverManager::class)->getAdapter($this->model);

        if ($driver) {
            try {
                $driver->teardown($this->model);
            } catch (\Throwable $e) {
                Log::warning(__METHOD__ . " | Driver teardown failed for gateway {$this->model->uuid}, continuing: " . $e->getMessage());
            }
        }

        //  Unscoped: this is an internal system cleanup write, not a user-facing query.
        //  Networks carries AuthorizationScope + LimitScope as global scopes: if this
        //  action runs where AuthorizationScope can't resolve a role for the current
        //  context (e.g. a queue worker without a re-established user/account), it falls
        //  back to AnonymousRole, which restricts the query to is_public=true - VDC
        //  networks are always created is_public=false, so the plain (scoped) version of
        //  this call could silently match zero rows, leaving iaas_gateway_id pointed at
        //  the gateway we just tore down and permanently blocking ProvisionGateway's
        //  already-has-a-gateway guard from ever clearing.
        Networks::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_gateway_id', $this->model->id)
            ->update(['iaas_gateway_id' => null]);

        $vm = $this->model->virtualMachines;

        if ($vm) {
            dispatch(new \NextDeveloper\IAAS\Actions\VirtualMachines\Delete($vm));
        }

        GatewaysService::delete($this->model->uuid);

        $this->setProgress(100, 'Gateway deleted');
    }
}
