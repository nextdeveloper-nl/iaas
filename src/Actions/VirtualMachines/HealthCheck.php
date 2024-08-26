<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action converts the virtual machine into a template
 */
class HealthCheck extends AbstractAction
{
    public const EVENTS = [
        'checked:NextDeveloper\IAAS\VirtualMachines',
        'healthy:NextDeveloper\IAAS\VirtualMachines',
        'stopped:NextDeveloper\IAAS\VirtualMachines',
        'halted:NextDeveloper\IAAS\VirtualMachines',
        'running:NextDeveloper\IAAS\VirtualMachines',
        'paused:NextDeveloper\IAAS\VirtualMachines',
        'resumed:NextDeveloper\IAAS\VirtualMachines',
        'health-check-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->queue = 'iaas-health-check';

        $this->model = $vm;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
