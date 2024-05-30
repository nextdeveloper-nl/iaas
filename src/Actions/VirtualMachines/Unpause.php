<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action pauses the virtual machine
 */
class Unpause extends AbstractAction
{
    public const EVENTS = [
        'unpausing:NextDeveloper\IAAS\VirtualMachines',
        'unpaused:NextDeveloper\IAAS\VirtualMachines',
        'unpause-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $vm;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine unpausing');

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] != 'paused') {
            $this->setProgress(100, 'We cannot unpause the virtual machine. It is not paused.');
            Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        VirtualMachinesXenService::unpause($this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] == 'running') {
            $this->setProgress(100, 'We unpaused the virtual machine. It is now running.');
            Events::fire('unpaused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            $this->setProgress(100, 'We cannot unpause the virtual machine. It is now ' . $vmParams['power-state'] . '.');
            Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        $this->setProgress(100, 'Virtual machine unpaused');
    }
}
