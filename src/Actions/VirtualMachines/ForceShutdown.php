<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action unplugs the virtual machine
 */
class ForceShutdown extends AbstractAction
{
    public const EVENTS = [
        'unplugging:NextDeveloper\IAAS\VirtualMachines',
        'unplugged:NextDeveloper\IAAS\VirtualMachines',
        'halted:NextDeveloper\IAAS\VirtualMachines',
        'unplug-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine hard shutdown');

        Events::fire('unplugging:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $vm = VirtualMachinesXenService::forceShutdown($this->model);
        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] != 'halted') {
            $this->setProgress(100, 'Virtual machine failed to hard shutdown');
            Events::fire('unplug-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        Events::fire('unpluged:NextDeveloper\IAAS\VirtualMachines', $this->model);
        Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine halted');
    }
}
