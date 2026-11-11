<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This action locks the Virtual Machine (sets is_locked to true)
 */
class Lock extends AbstractAction
{
    public const EVENTS = [
        'locking:NextDeveloper\IAAS\VirtualMachines',
        'locked:NextDeveloper\IAAS\VirtualMachines',
        'lock-failed:NextDeveloper\IAAS\VirtualMachines',
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct(null, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Virtual machine is being locked.');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        Events::fire('locking:NextDeveloper\\IAAS\\VirtualMachines', $this->model);

        //  Any failure while updating the flag (observer/policy exception) used to leave the action
        //  unfinished forever; now it is closed with an error so the panel does not wait on it.
        try {
            $this->model->update([
                'is_locked' => true
            ]);
        } catch (\Throwable $e) {
            Events::fire('lock-failed:NextDeveloper\\IAAS\\VirtualMachines', $this->model);
            $this->setFinishedWithError('Cannot lock the virtual machine: ' . $e->getMessage());
            return;
        }

        Events::fire('locked:NextDeveloper\\IAAS\\VirtualMachines', $this->model);

        //  Explicitly close the action on success.
        $this->setFinished('Virtual machine is locked');
    }
}
