<?php

namespace NextDeveloper\IAAS\Actions\VirtualNetworkCards;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;

/**
 * This action converts the virtual machine into a template
 */
class AddRandomIpV4 extends AbstractAction
{
    public const EVENTS = [
        'adding-random-ip:NextDeveloper\IAAS\VirtualNetworkCards',
        'added-random-ip:NextDeveloper\IAAS\VirtualNetworkCards',
        'failed-adding-random-ip:NextDeveloper\IAAS\VirtualNetworkCards'
    ];

    public function __construct(VirtualNetworkCards $vnc)
    {
        $this->model = $vnc;

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
