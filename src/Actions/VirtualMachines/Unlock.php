<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action unlocks the Virtual Machine (sets is_locked to false)
 */
class Unlock extends AbstractAction
{
    public const EVENTS = [
        'unlocking:NextDeveloper\IAAS\VirtualMachines',
        'unlocked:NextDeveloper\IAAS\VirtualMachines',
        'unlock-failed:NextDeveloper\IAAS\VirtualMachines',
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct(null, $previous);
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

        Events::fire('unlocking:NextDeveloper\\IAAS\\VirtualMachines', $this->model);

        //  Any failure while updating the flag (observer/policy exception) used to leave the action
        //  unfinished forever; now it is closed with an error so the panel does not wait on it.
        try {
            $this->model->update([
                'is_locked' => false
            ]);
        } catch (\Throwable $e) {
            Events::fire('unlock-failed:NextDeveloper\\IAAS\\VirtualMachines', $this->model);
            $this->setFinishedWithError('Cannot unlock the virtual machine: ' . $e->getMessage());
            return;
        }

        Events::fire('unlocked:NextDeveloper\\IAAS\\VirtualMachines', $this->model);

        //  Explicitly close the action on success.
        $this->setFinished('Virtual machine is unlocked');
    }
}
