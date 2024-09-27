<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action starts the Virtual Machine
 */
class Start extends AbstractAction
{
    public const EVENTS = [
        'starting:NextDeveloper\IAAS\VirtualMachines',
        'started:NextDeveloper\IAAS\VirtualMachines',
        'start-failed:NextDeveloper\IAAS\VirtualMachines'
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

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        Events::fire('starting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        (new Fix($this->model))->handle();

        $vm = VirtualMachinesXenService::start($this->model);
        $vmParams = VirtualMachinesXenService::getVmParameters($vm);

        if(!array_key_exists('power_state', $vmParams)) {
            //  The VM must not be available to be honest. So we should make a health check here.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            $job = new HealthCheck($this->model);
            $id = $job->getActionId();

            dispatch($job)->onQueue('iaas');

            $this->setProgress(100, 'Checking the health of the VM. ' .
                'We suspect something is happening to it.');

            return $id;
        }

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[Start@handle] I am starting the' .
                ' VM (' . $vm->name. '/' . $vm->uuid . ')');

        if($vmParams['power-state'] != 'running') {
            $this->setProgress(100, 'Virtual machine failed to start');
            Events::fire('start-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model->update([
            'status'            =>  'running',
            'hypervisor_data'   =>  $vmParams
        ]);

        Events::fire('started:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
