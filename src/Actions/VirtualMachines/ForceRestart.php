<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;

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

        $configIsoDriver = app(VirtualMachineManager::class)->getAdapter($this->model);

        if($configIsoDriver instanceof ConfigurationIsoCapableInterface) {
            $configIsoDriver->regenerateConfigurationIso($this->model);
        } else {
            VirtualMachinesXenService::updateConfigurationIso($this->model);
        }

        $this->model = app(VirtualMachineManager::class)->sync($this->model);

        //  Removed the power-state / 'checking-health' branch here: health check is retired, so
        //  force restart no longer flips the VM into 'checking-health'. The status check below
        //  (after sync) is the only guard.
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
