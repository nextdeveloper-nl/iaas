<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\Models\Actions;
use NextDeveloper\Commons\Helpers\StateHelper;
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

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct($previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Starting virtual machine job started.');

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

        $result = VirtualMachinesXenService::start($this->model);

        if($result['error'] != '') {
            if($result['error'] == 'Error: No matching VMs found') {
                StateHelper::setState($this->model, 'cannot-find-vm', 'true', StateHelper::STATE_ERROR);

                dispatch(new HealthCheck($this->model, null, $this));

                $this->setFinishedWithError('This VM needs a health check. That is why I am finishing this action here.');
                //  We need to finish this action
                return;
            }
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

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[Start@handle] I am starting the' .
                ' VM (' . $this->model->name. '/' . $this->model->uuid . ')');

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

        $this->setProgress(100, 'Virtual machine started');
    }
}
