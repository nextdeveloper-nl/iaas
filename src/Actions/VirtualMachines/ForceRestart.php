<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;

/**
 * This action unplugs the virtual machine, and then plugs it back in
 */
class ForceRestart extends AbstractAction
{
    public const EVENTS = [
        'unplugging:NextDeveloper\IAAS\VirtualMachines',
        'unplugged:NextDeveloper\IAAS\VirtualMachines',
        'plugging:NextDeveloper\IAAS\VirtualMachines',
        'plugged:NextDeveloper\IAAS\VirtualMachines',
        'restart-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine force restarting');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot force restart this the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        Events::fire('unplugging:NextDeveloper\IAAS\VirtualMachines', $this->model);

        //  Not routed through VirtualMachineManager: config-ISO regeneration has no
        //  capability interface yet - see docs/hypervisor-driver-architecture.md.
        VirtualMachinesXenService::updateConfigurationIso($this->model);

        $this->model = app(VirtualMachineManager::class)->sync($this->model);

        if(!$this->model->hypervisor_data || !array_key_exists('power-state', $this->model->hypervisor_data)) {
            //  The VM must not be available to be honest. So we should make a health check here.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            $job = new HealthCheck($this->model, null, $this);
            $id = $job->getActionId();

            dispatch($job)->onQueue('iaas');

            $this->setProgress(100, 'Checking the health of the VM. ' .
                'We suspect something is happening to it.');

            return $id;
        }

        if($this->model->status != 'running') {
            CommentsService::createSystemComment('We cannot hard restart the virtual machine.', $this->model);
            $this->setFinishedWithError('We cannot hard restart the virtual machine. It is not running.');
            Events::fire('restart-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model = app(VirtualMachineManager::class)->restart($this->model, true);

        Events::fire('unplugged:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->status == 'running') {
            CommentsService::createSystemComment('Virtual machine is hard restarted successfully.', $this->model);
            $this->setProgress(100, 'We hard restarted the virtual machine. It is now running.');
            Events::fire('plugged:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            CommentsService::createSystemComment('Cannot hard restart the virtual machine.', $this->model);
            $this->setFinishedWithError('We cannot hard restart the virtual machine. It is now ' . $this->model->status . '.');
            Events::fire('restart-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->setProgress(100, 'Virtual machine restarted');
    }
}
