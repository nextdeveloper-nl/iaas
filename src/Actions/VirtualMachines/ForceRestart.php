<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

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

        Events::fire('unplugging:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] != 'running') {
            $this->setFinishedWithError('We cannot hard restart the virtual machine. It is not running.');
            Events::fire('restart-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $result = VirtualMachinesXenService::forceRestart($this->model);

        Events::fire('unplugged:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] == 'running') {
            $this->setProgress(100, 'We hard restarted the virtual machine. It is now running.');
            Events::fire('plugged:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            $this->setFinishedWithError('We cannot hard restart the virtual machine. It is now ' . $vmParams['power-state'] . '.');
            Events::fire('restart-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        $this->setProgress(100, 'Virtual machine restarted');
    }
}
