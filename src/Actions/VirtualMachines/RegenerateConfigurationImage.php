<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;

/**
 * This action regenerates and uploads the configuration image (config ISO) for the
 * virtual machine, without restarting or starting it. Useful for testing config
 * changes on a VM that is already running.
 */
class RegenerateConfigurationImage extends AbstractAction
{
    public const EVENTS = [
        'regenerating-configuration-image:NextDeveloper\IAAS\VirtualMachines',
        'configuration-image-regenerated:NextDeveloper\IAAS\VirtualMachines',
        'configuration-image-regeneration-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Regenerating configuration image');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        Events::fire('regenerating-configuration-image:NextDeveloper\IAAS\VirtualMachines', $this->model);

        try {
            $driver = app(VirtualMachineManager::class)->getAdapter($this->model);

            $result = $driver instanceof ConfigurationIsoCapableInterface
                ? $driver->regenerateConfigurationIso($this->model)
                : VirtualMachinesXenService::updateConfigurationIso($this->model);
        } catch (\Exception $e) {
            CommentsService::createSystemComment('Regenerating configuration image failed: ' . $e->getMessage(), $this->model);
            Events::fire('configuration-image-regeneration-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            $this->setFinishedWithError('Regenerating configuration image failed: ' . $e->getMessage());
            return;
        }

        if(!$result) {
            CommentsService::createSystemComment('Regenerating configuration image failed: no ISO repository found for this VM.', $this->model);
            Events::fire('configuration-image-regeneration-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            $this->setFinishedWithError('Regenerating configuration image failed: no ISO repository found for this VM.');
            return;
        }

        Events::fire('configuration-image-regenerated:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setFinished('Configuration image regenerated');
    }
}
