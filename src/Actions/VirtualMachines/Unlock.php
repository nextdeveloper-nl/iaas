<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action starts the Virtual Machine
 */
class Unlock extends AbstractAction
{
    public const EVENTS = [
        'locking:NextDeveloper\IAAS\VirtualMachines',
        'locked:NextDeveloper\IAAS\VirtualMachines',
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct($previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Virtual machine is being unlocked.');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        Events::fire('locking:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->model->update([
            'is_locked' => false
        ]);

        Events::fire('unlocked:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine is unlocked');
    }
}
