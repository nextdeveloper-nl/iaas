<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action shutdowns the Virtual Machine
 */
class Shutdown extends AbstractAction
{
    public const EVENTS = [
        'halting:NextDeveloper\IAAS\VirtualMachines',
        'halted:NextDeveloper\IAAS\VirtualMachines',
        'shutdown-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Shutdown virtual machine started');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot shutdown this the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        Events::fire('halting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        (new Fix($this->model))->handle();

        try {
            $vm = VirtualMachinesXenService::shutdown($this->model);
            $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

            if($vmParams['power-state'] != 'halted') {
                CommentsService::createSystemComment('Virtual machine is not running or halted', $this->model);
                $this->setProgress(100, 'Virtual machine failed to shutdown');
                Events::fire('shutdown-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
                return;
            }

            $this->model->update([
                'status'            =>  'halted',
                'hypervisor_data'   =>  $vmParams
            ]);
        } catch (\Exception $e) {
            CommentsService::createSystemComment('Virtual machine shutdown has failed. This was unexpected so checking the health of the VM.', $this->model);
            Events::fire('shutdown-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            dispatch(new HealthCheck($this->model));
        }

        CommentsService::createSystemComment('Virtual machine is found as halted', $this->model);
        Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine shutdown');
    }
}
