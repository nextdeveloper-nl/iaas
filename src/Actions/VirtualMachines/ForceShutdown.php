<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;

/**
 * This action unplugs the virtual machine
 */
class ForceShutdown extends AbstractAction
{
    public const EVENTS = [
        'unplugging:NextDeveloper\IAAS\VirtualMachines',
        'unplugged:NextDeveloper\IAAS\VirtualMachines',
        'halted:NextDeveloper\IAAS\VirtualMachines',
        'unplug-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine hard shutdown');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        //  Checking if the VM is lost;
        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot force shutdown this the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        Events::fire('unplugging:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->model = app(VirtualMachineManager::class)->stop($this->model, true);

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

        if($this->model->status != 'halted') {
            CommentsService::createSystemComment('Virtual machine failed to hard shutdown', $this->model);
            $this->setProgress(100, 'Virtual machine failed to hard shutdown');
            Events::fire('unplug-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        CommentsService::createSystemComment('Virtual machine is successfully unplugged or halted.', $this->model);
        Events::fire('unpluged:NextDeveloper\IAAS\VirtualMachines', $this->model);
        Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine halted');
    }
}
