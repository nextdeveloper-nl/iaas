<?php

namespace NextDeveloper\IAAS\Actions\Gateways;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action converts the virtual machine into a template
 */
class Commit extends AbstractAction
{
    public const EVENTS = [
        'committing:NextDeveloper\IAAS\Gateways',
        'committed:NextDeveloper\IAAS\Gateways',
        'commit-failed:NextDeveloper\IAAS\Gateways'
    ];

    public function __construct(Gateways $gateway)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $gateway;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
