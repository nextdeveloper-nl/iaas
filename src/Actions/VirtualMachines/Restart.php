<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action restarts the Virtual Machine
 */
class Restart extends AbstractAction
{
    public const EVENTS = [
        'restarting:NextDeveloper\IAAS\VirtualMachines',
        'restarted:NextDeveloper\IAAS\VirtualMachines',
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
        $this->setProgress(0, 'Restarting the virtual machine');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        (new Fix($this->model))->handle();

        Events::fire('restarting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] != 'running') {
            $this->setProgress(100, 'We cannot restart the virtual machine. It is not running.');
            Events::fire('restart-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $result = VirtualMachinesXenService::restart($this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] == 'running') {
            $this->setProgress(100, 'We restarted the virtual machine. It is now running.');
            Events::fire('restarted:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        Events::fire('restarted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine restarted');
    }
}
