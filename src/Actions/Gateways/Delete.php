<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\GatewaysService;
use NextDeveloper\IAAS\Services\Hypervisors\GatewayDriverManager;

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

        Networks::where('iaas_gateway_id', $this->model->id)->update(['iaas_gateway_id' => null]);

        $vm = $this->model->virtualMachines;

        if ($vm) {
            dispatch(new \NextDeveloper\IAAS\Actions\VirtualMachines\Delete($vm));
        }

        GatewaysService::delete($this->model->uuid);

        $this->setProgress(100, 'Gateway deleted');
    }
}
