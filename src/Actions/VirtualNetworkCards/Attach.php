<?php

namespace NextDeveloper\IAAS\Actions\VirtualNetworkCards;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action converts the virtual machine into a template
 */
class Attach extends AbstractAction
{
    public const EVENTS = [
        'attaching:NextDeveloper\IAAS\VirtualNetworkCards',
        'attached:NextDeveloper\IAAS\VirtualNetworkCards',
        'attach-failed:NextDeveloper\IAAS\VirtualNetworkCards'
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
