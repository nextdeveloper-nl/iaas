<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action creates a VDI from scratch
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\VirtualDiskImages',
        'created:NextDeveloper\IAAS\VirtualDiskImages',
        'create-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $diskImage, $params = null, $previous = null)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $diskImage;

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
