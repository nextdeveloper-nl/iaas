<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action converts the virtual machine into a template
 */
class Delete extends AbstractAction
{
    public const EVENTS = [
        'deleting:NextDeveloper\IAAS\VirtualMachines',
        'deleted:NextDeveloper\IAAS\VirtualMachines',
        'delete-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm, array $options = [])
    {
        $this->queue = 'iaas';

        $this->model = $vm;

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Delete virtual machine started');
        Events::fire('deleting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

//        if($this->model->deleted_at != null) {
//            $this->setFinished('I cannot complete this process because the VM is already deleted');
//            Events::fire('deleted:NextDeveloper\IAAS\VirtualMachines', $this->model);
//            return;
//        }

        if($this->model->is_locked) {
            $this->setFinished('I cannot complete this process because the VM is locked');
            Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        Events::fire('deleting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        try {
            VirtualMachinesXenService::forceShutdown($this->model);
            VirtualMachinesXenService::destroyVm($this->model);

            VirtualMachinesService::delete($this->model->uuid);

            $this->model->delete();
        } catch (\Exception $e) {
            Events::fire('deleted:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        Events::fire('deleted:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setProgress(100, 'Virtual machine removed');
    }
}
