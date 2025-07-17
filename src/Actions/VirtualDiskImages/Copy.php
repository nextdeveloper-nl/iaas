<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;

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
