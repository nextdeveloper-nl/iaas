<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

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
        trigger_error('This action is not yet implemented', E_USER_ERROR);

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
