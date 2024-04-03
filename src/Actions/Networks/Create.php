<?php

namespace NextDeveloper\IAAS\Actions\Networks;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action converts the virtual machine into a template
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\Networks',
        'created:NextDeveloper\IAAS\Networks',
        'create-failed:NextDeveloper\IAAS\Networks'
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
