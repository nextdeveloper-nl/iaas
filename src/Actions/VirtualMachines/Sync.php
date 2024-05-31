<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action converts the virtual machine into a template
 */
class Sync extends AbstractAction
{
    public const EVENTS = [
        'syncing:NextDeveloper\IAAS\VirtualMachines',
        'synced:NextDeveloper\IAAS\VirtualMachines',
        'sync-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        Events::fire('syncing:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $params = VirtualMachinesXenService::getVmParameters($this->model);

        if(!$params) {
            $this->setProgress(100, 'Virtual machine failed to sync');
            Events::fire('sync-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        dd($params);

        $this->model->update([
            'status'    =>  $params['power-state'],
            'cpu'       =>  $params['VCPUs-max'],
            'ram'       =>  $params['memory-static-max'] / 1024 / 1024 / 1024,
            'is_snapshot'   =>  $params['is-a-snapshot'] === 'true',
            'domain_type'   =>  $params['hvm'] === 'true' ? 'HVM' : 'PV',
            'hypervisor_data'   =>  $params
        ]);

        Events::fire('synced:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
