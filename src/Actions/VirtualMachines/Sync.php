<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action converts the virtual machine into a template
 */
class Sync extends AbstractAction
{
    public const EVENTS = [
        'syncing:NextDeveloper\IAAS\VirtualMachines',
        'synced:NextDeveloper\IAAS\VirtualMachines',
        'sync-failed:NextDeveloper\IAAS\VirtualMachines'
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

        (new Fix($this->model))->handle();

        Events::fire('syncing:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $params = VirtualMachinesXenService::getVmParameters($this->model);

        if(!array_key_exists('power_state', $params)) {
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

        if(!$params) {
            $this->setProgress(100, 'Virtual machine failed to sync');
            Events::fire('sync-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model->update([
            'status'    =>  $params['power-state'],
            'cpu'       =>  $params['VCPUs-max'],
            'ram'       =>  $params['memory-static-max'] / 1024 / 1024 / 1024,
            'is_snapshot'   =>  $params['is-a-snapshot'] === 'true',
            'domain_type'   =>  $params['hvm'] === 'true' ? 'HVM' : 'PV',
            'hypervisor_data'   =>  $params
        ]);

        Events::fire('synced:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
