<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action regenerates and uploads the configuration ISO for the virtual machine,
 * without restarting or starting it. Useful for testing config ISO changes on a VM
 * that is already running.
 */
class UpdateConfigurationIso extends AbstractAction
{
    public const EVENTS = [
        'updating-configuration-iso:NextDeveloper\IAAS\VirtualMachines',
        'configuration-iso-updated:NextDeveloper\IAAS\VirtualMachines',
        'configuration-iso-update-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Updating configuration ISO');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        Events::fire('updating-configuration-iso:NextDeveloper\IAAS\VirtualMachines', $this->model);

        try {
            $result = VirtualMachinesXenService::updateConfigurationIso($this->model);
        } catch (\Exception $e) {
            CommentsService::createSystemComment('Updating configuration ISO failed: ' . $e->getMessage(), $this->model);
            Events::fire('configuration-iso-update-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            $this->setFinishedWithError('Updating configuration ISO failed: ' . $e->getMessage());
            return;
        }

        if(!$result) {
            CommentsService::createSystemComment('Updating configuration ISO failed: no ISO repository found for this VM.', $this->model);
            Events::fire('configuration-iso-update-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            $this->setFinishedWithError('Updating configuration ISO failed: no ISO repository found for this VM.');
            return;
        }

        Events::fire('configuration-iso-updated:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setFinished('Configuration ISO updated');
    }
}
