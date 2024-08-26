<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action converts the virtual machine into a template
 */
class Backup extends AbstractAction
{
    public const EVENTS = [
        'backing-up:NextDeveloper\IAAS\VirtualMachines',
        'backed-up:NextDeveloper\IAAS\VirtualMachines',
        'backup-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->setProgress(10, 'Taking the snapshot of the virtual machine');

        VirtualMachinesXenService::takeSnapshot($this->model);

        VirtualMachinesXenService::convertSnapshotToVm($this->model, $name);

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
