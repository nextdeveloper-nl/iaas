<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;

/**
 * This action pauses the virtual machine
 */
class Unpause extends AbstractAction
{
    public const EVENTS = [
        'unpausing:NextDeveloper\IAAS\VirtualMachines',
        'unpaused:NextDeveloper\IAAS\VirtualMachines',
        'unpause-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine unpausing');
        Events::fire('unpausing:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot unpause this the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        $this->model = app(VirtualMachineManager::class)->sync($this->model);

        if(!$this->model->hypervisor_data || !array_key_exists('power-state', $this->model->hypervisor_data)) {
            //  The hypervisor did not return a usable power-state, so this VM's state
            //  cannot be trusted right now. HealthCheck (which used to investigate this
            //  further) has been retired - flag it for manual investigation instead of
            //  dispatching a no-op job.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            $this->setFinishedWithError('Could not determine the virtual machine\'s state after this operation. It has been marked for manual investigation.');

            return;
        }

        if($this->model->status != 'paused') {
            CommentsService::createSystemComment('We cannot unpause the virtual machine. It is still in paused state', $this->model);
            $this->setProgress(100, 'We cannot unpause the virtual machine. It is not paused.');
            Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model = app(VirtualMachineManager::class)->resume($this->model);

        if($this->model->status == 'running') {
            CommentsService::createSystemComment('Virtual machine is now unpaused and running.', $this->model);
            $this->setProgress(100, 'We unpaused the virtual machine. It is now running.');
            Events::fire('unpaused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            CommentsService::createSystemComment('We cannot unpause the virtual machine. The state is now: ' . $this->model->status, $this->model);
            $this->setProgress(100, 'We cannot unpause the virtual machine. It is now ' . $this->model->status . '.');
            Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->setProgress(100, 'Virtual machine unpaused');
    }
}
