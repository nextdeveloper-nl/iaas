<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\Models\Actions;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action converts the virtual machine into a template
 */
class HealthCheck extends AbstractAction
{
    public const EVENTS = [
        'checked:NextDeveloper\IAAS\VirtualMachines',
        'healthy:NextDeveloper\IAAS\VirtualMachines',
        'stopped:NextDeveloper\IAAS\VirtualMachines',
        'halted:NextDeveloper\IAAS\VirtualMachines',
        'running:NextDeveloper\IAAS\VirtualMachines',
        'paused:NextDeveloper\IAAS\VirtualMachines',
        'resumed:NextDeveloper\IAAS\VirtualMachines',
        'health-check-failed:NextDeveloper\IAAS\VirtualMachines',
        'vm-is-lost:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous)
    {
        $this->queue = 'iaas-health-check';

        $this->model = $vm;

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->setProgress(10, 'Marking the server as checking health');

        $this->model->status = 'checking-health';
        $this->model->saveQuietly();

        $this->setProgress(50, 'Checking if the virtual machine is alive');
        $isVmThere = VirtualMachinesXenService::checkIfVmIsThere($this->model);

        Log::info(__METHOD__ . ' | We checked if VM (' . $this->model->uuid
            . ' exists, and the result is: ' . ($isVmThere ? 'TRUE' : 'FALSE'));

        if(!$isVmThere) {
            //  This means that the VM is not there. Lets check if its already dead or not;

            //  Oops the VM is lost! We should mark it as lost
            $this->model->is_lost = true;
            $this->model->status = 'lost';
            $this->model->deleted_at = now();
            $this->model->saveQuietly();

            Log::info(__METHOD__ . ' | Marked VM as lost: ' . $this->model->name);

            Events::fire('vm-is-lost:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->setProgress(100, 'Virtual machine marked as lost.');
            return;
        }

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        $this->setProgress(75, 'Marking the server power state as: ' . $vmParams['power-state']);

        $this->model->updateQuietly([
            'status'    =>  $vmParams['power-state']
        ]);

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
