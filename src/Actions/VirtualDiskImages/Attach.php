<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action attaches the virtual disk image to the virtual machine
 */
class Attach extends AbstractAction
{
    public const EVENTS = [
        'attaching:NextDeveloper\IAAS\VirtualDiskImages',
        'attached:NextDeveloper\IAAS\VirtualDiskImages',
        'attach-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $vm)
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
