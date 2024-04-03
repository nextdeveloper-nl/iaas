<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action copies the virtual disk image
 */
class Copy extends AbstractAction
{
    public const EVENTS = [
        'copying:NextDeveloper\IAAS\VirtualDiskImages',
        'copied:NextDeveloper\IAAS\VirtualDiskImages',
        'copy-failed:NextDeveloper\IAAS\VirtualDiskImages'
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
