<?php

namespace NextDeveloper\IAAS\Actions\VirtualNetworkCards;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\NetworkCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action detaches a virtual network card from its virtual machine on the hypervisor.
 */
class Detach extends AbstractAction
{
    public const EVENTS = [
        'detaching:NextDeveloper\IAAS\VirtualNetworkCards',
        'detached:NextDeveloper\IAAS\VirtualNetworkCards',
        'detach-failed:NextDeveloper\IAAS\VirtualNetworkCards'
    ];

    public function __construct(VirtualNetworkCards $vif, $params = null, $previous = null)
    {
        $this->model = $vif;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual network card detach');

        $vif = $this->model;

        Events::fire('detaching:NextDeveloper\IAAS\VirtualNetworkCards', $vif);

        if ($vif->hypervisor_uuid == null) {
            //  Network card was never attached on the hypervisor (eg. still in draft state).
            $vif->updateQuietly([
                'status' =>  'detached'
            ]);

            Events::fire('detached:NextDeveloper\IAAS\VirtualNetworkCards', $vif);
            $this->setFinished('Network card was not attached, marked as detached.');
            return;
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vif->iaas_virtual_machine_id)
            ->first();

        if (!$vm || $vm->hypervisor_data == null) {
            Log::info('[VirtualNetworkCards@Detach] Seems like VM is still in draft state or is missing, ' .
                'so there is nothing to detach on the hypervisor.');

            $vif->updateQuietly([
                'hypervisor_uuid'   => null,
                'hypervisor_data'   => null,
                'status'            => 'detached'
            ]);

            Events::fire('detached:NextDeveloper\IAAS\VirtualNetworkCards', $vif);
            $this->setFinished('Network card detached.');
            return;
        }

        $this->setProgress(50, 'Detaching the network card from the hypervisor.');

        $driver = app(VirtualMachineManager::class)->getAdapter($vm);

        if ($driver instanceof NetworkCapableInterface) {
            $driver->destroyNetworkCard($vif);
        } else {
            VirtualMachinesXenService::destroyVif($vm, $vif->hypervisor_data['uuid']);
        }

        $vif->updateQuietly([
            'hypervisor_uuid'   => null,
            'hypervisor_data'   => null,
            'status'            => 'detached'
        ]);

        Events::fire('detached:NextDeveloper\IAAS\VirtualNetworkCards', $vif);

        $this->setFinished('Network card detached.');
    }
}
