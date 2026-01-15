<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action pauses the virtual machine
 */
class Pause extends AbstractAction
{
    public const EVENTS = [
        'pausing:NextDeveloper\IAAS\VirtualMachines',
        'paused:NextDeveloper\IAAS\VirtualMachines',
        'pause-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Pausing virtual machine started');
        Events::fire('pausing:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot pause this the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        (new Fix($this->model))->handle();

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] != 'running') {
            $this->setProgress(100, 'We cannot pause the virtual machine. It is not halted.');
            Events::fire('pause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        VirtualMachinesXenService::pause($this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] == 'paused') {
            CommentsService::createSystemComment('Virtual machine paused', $this->model);
            $this->setProgress(100, 'We paused the virtual machine. It is now paused.');
            Events::fire('paused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            CommentsService::createSystemComment('Cannot pause VM.', $this->model);
            $this->setProgress(100, 'We cannot pause the virtual machine. It is now ' . $vmParams['power-state'] . '.');
            Events::fire('pause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        $this->setProgress(100, 'Virtual machine paused');
    }
}
