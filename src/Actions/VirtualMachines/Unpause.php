<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action pauses the virtual machine
 */
class Unpause extends AbstractAction
{
    public const EVENTS = [
        'unpausing:NextDeveloper\IAAS\VirtualMachines',
        'unpaused:NextDeveloper\IAAS\VirtualMachines',
        'unpause-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine unpausing');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if(!array_key_exists('power_state', $vmParams)) {
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

        if($vmParams['power-state'] != 'paused') {
            $this->setProgress(100, 'We cannot unpause the virtual machine. It is not paused.');
            Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        VirtualMachinesXenService::unpause($this->model);

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if($vmParams['power-state'] == 'running') {
            $this->setProgress(100, 'We unpaused the virtual machine. It is now running.');
            Events::fire('unpaused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        } else {
            $this->setProgress(100, 'We cannot unpause the virtual machine. It is now ' . $vmParams['power-state'] . '.');
            Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        }

        $this->model->update([
            'status'            =>  $vmParams['power-state'],
            'hypervisor_data'   =>  $vmParams
        ]);

        $this->setProgress(100, 'Virtual machine unpaused');
    }
}
