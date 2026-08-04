<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Jobs\VirtualMachines\GenerateCloudInitImage;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

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

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot start this the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        Events::fire('starting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        dispatch(new Fix($this->model));

        if(config('iaas.cloud-init.available')) {
            Log::info('[Start@handle] . Cloud init is available. So I am moving to configuration iso update.');
            //  Here we need to deploy the configuration iso

            $configImage = RepositoryImagesService::getCloudInitImage($this->model);

            if(!$configImage) {
                //  If we do not have a configuration image we will create it
                Log::info('[Start@handle] . No configuration image found. Dispatching the job to create it.');
                (new GenerateCloudInitImage($this->model))->handle();

                $configImage = RepositoryImagesService::getCloudInitImage($this->model);
            }

            //  Check if the CDROM is mounted, if not we will mount and add the configuration iso
            $cdrom = VirtualMachinesService::getCdrom($this->model);

            if($cdrom == null) {
                VirtualMachinesXenService::updateConfigurationIso($this->model);
                VirtualMachinesXenService::mountCD($this->model, $configImage, true);
            } else {
                if($cdrom->size == 0) {
                    VirtualMachinesXenService::updateConfigurationIso($this->model);
                    VirtualMachinesXenService::mountCD($this->model, $configImage, true);
                } else {
                    Log::info(__METHOD__ . ' CDROM is already mounted. Not remounting.');
                }
            }
        }

        //  Kept as a direct call (not routed through VirtualMachineManager): the raw `xe`
        //  error string is inspected below for a specific failure mode ("no matching VMs")
        //  that has no generalized equivalent in the driver interface yet - see
        //  docs/hypervisor-driver-architecture.md. Everything after this stays migrated.
        $result = VirtualMachinesXenService::start($this->model);

        if($result['error'] != '') {
            if($result['error'] == 'Error: No matching VMs found') {
                StateHelper::setState($this->model, 'cannot-find-vm', 'true', StateHelper::STATE_ERROR);

                $this->setFinishedWithError('This VM could not be found on the hypervisor. It has been marked for manual investigation.');
                return;
            }
        }

        $this->model = app(VirtualMachineManager::class)->sync($this->model);
        $vmParams = $this->model->hypervisor_data;

        if(!array_key_exists('power-state', $vmParams)) {
            //  The hypervisor did not return a usable power-state, so this VM's state
            //  cannot be trusted right now. HealthCheck (which used to investigate this
            //  further) has been retired - flag it for manual investigation instead of
            //  dispatching a no-op job.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            $this->setFinishedWithError('Could not determine the virtual machine\'s state after this operation. It has been marked for manual investigation.');

            return;
        }

        if(config('leo.debug.iaas.compute_members'))
            Log::error('[Start@handle] I am starting the' .
                ' VM (' . $this->model->name. '/' . $this->model->uuid . ')');

        if($vmParams['power-state'] != 'running') {
            CommentsService::createSystemComment('Failed to start the virtual machine.', $this->model);
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
