<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;

/**
 * This action converts the virtual machine into a template
 */
class Snapshot extends AbstractAction
{
    public const EVENTS = [
        'taking-snapshot:NextDeveloper\IAAS\VirtualMachines',
        'snapshot-taken:NextDeveloper\IAAS\VirtualMachines',
        'snapshot-failed:NextDeveloper\IAAS\VirtualMachines'
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
        Events::fire('taking-snapshot:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        $this->setProgress(10, 'Taking the snapshot of the virtual machine');

        //  VirtualMachineManager::createSnapshot() throws on a failed xe snapshot instead
        //  of silently continuing with an empty hypervisor_uuid the way the old inline
        //  code here did - a failed snapshot now fails this action instead of creating a
        //  broken snapshot row and reporting success.
        $snapshot = app(VirtualMachineManager::class)->createSnapshot($this->model, 'Snapshot of ' . $this->model->name);

        StateHelper::setState($this->model, 'snapshot', 'Snapshot taken successfully', StateHelper::STATE_SUCCESS);

        Events::fire('snapstot-taken:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setProgress(100, 'Virtual machine initiated');
    }
}
