<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachineImages;

use JetBrains\PhpStorm\NoReturn;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action converts the virtual machine into a template
 */
class Import extends AbstractAction
{
    public const EVENTS = [
        'importing:NextDeveloper\IAAS\VirtualMachineImages',
        'imported:NextDeveloper\IAAS\VirtualMachineImages',
        'import-failed:NextDeveloper\IAAS\VirtualMachineImages'
    ];

    #[NoReturn] public function __construct(VirtualMachines $vm)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $vm;
    }

    public function handle(): void
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
