<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
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

        Events::fire('halting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        (new Fix($this->model))->handle();

        try {
            $vm = VirtualMachinesXenService::shutdown($this->model);
            $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

            if($vmParams['power-state'] != 'halted') {
                $this->setProgress(100, 'Virtual machine failed to shutdown');
                Events::fire('shutdown-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
                return;
            }

            $this->model->update([
                'status'            =>  'halted',
                'hypervisor_data'   =>  $vmParams
            ]);
        } catch (\Exception $e) {
            Events::fire('shutdown-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            dispatch(new HealthCheck($this->model));
        }

        Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine shutdown');
    }
}
