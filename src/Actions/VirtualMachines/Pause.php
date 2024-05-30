<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action pauses the virtual machine
 */
class Pause extends AbstractAction
{
    public const EVENTS = [
        'pausing:NextDeveloper\IAAS\VirtualMachines',
        'paused:NextDeveloper\IAAS\VirtualMachines',
        'pause-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $vm;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] != 'running') {
            $this->setProgress(100, 'We cannot pause the virtual machine. It is not halted.');
            Events::fire('pause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        VirtualMachinesXenService::pause($this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] == 'paused') {
            $this->setProgress(100, 'We paused the virtual machine. It is now paused.');
            Events::fire('paused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            $this->setProgress(100, 'We cannot pause the virtual machine. It is now ' . $vmParams['power-state'] . '.');
            Events::fire('pause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        $this->setProgress(100, 'Virtual machine paused');
    }
}
