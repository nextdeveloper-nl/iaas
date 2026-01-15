<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

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

        $vm = VirtualMachinesXenService::forceShutdown($this->model);
        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if(!array_key_exists('power-state', $vmParams)) {
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

        if($vmParams['power-state'] != 'halted') {
            CommentsService::createSystemComment('Virtual machine failed to hard shutdown', $this->model);
            $this->setProgress(100, 'Virtual machine failed to hard shutdown');
            Events::fire('unplug-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        CommentsService::createSystemComment('Virtual machine is successfully unplugged or halted.', $this->model);
        Events::fire('unpluged:NextDeveloper\IAAS\VirtualMachines', $this->model);
        Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine halted');
    }
}
