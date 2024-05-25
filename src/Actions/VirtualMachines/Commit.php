<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action converts a draft virtual machine to a live virtual machine. This action should be triggered when the
 * virtual machine is in draft state and needs to go live. If the virtual machine state is not draft this action will
 * cancel itself.
 */
class Commit extends AbstractAction
{
    public const EVENTS = [
        'commiting:NextDeveloper\IAAS\VirtualMachines',
        'committed:NextDeveloper\IAAS\VirtualMachines',
        'commit-failed:NextDeveloper\IAAS\VirtualMachines'
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
