<?php

namespace NextDeveloper\IAAS\EventHandlers\VirtualMachines;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStats;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StatService;

/**
 * Class OnVirtualMachineUpdate
 *
 * This event handler is triggered when a virtual machine is updated. It updates the virtual machine's statistics
 * and logs the update event.
 *
 * @package NextDeveloper\IAAS\EventHandlers\VirtualMachines
 */
class OnVirtualMachineUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The virtual machine model instance.
     *
     * @var VirtualMachines
     */
    private $model;

    /**
     * OnVirtualMachineUpdate constructor.
     *
     * @param VirtualMachines $virtualMachine The virtual machine model instance.
     */
    public function __construct(VirtualMachines $virtualMachine)
    {
        $this->model = $virtualMachine;
    }

    /**
     * Handles the virtual machine update event.
     *
     * This function performs the following steps:
     * 1. Defines the object type for virtual machine statistics.
     * 2. Prepares the parameters for the statistics update.
     * 3. Calls the StatService to create the statistics entry.
     * 4. Logs the update event.
     */
    public function handle(): void
    {
        // Define the object type for virtual machine statistics
        $object = 'NextDeveloper\IAAS\Database\Models\VirtualMachineStats';

        // Prepare the parameters for the statistics update
        $params = [
            'cpu'                       => $this->model->cpu,
            'ram'                       => $this->model->ram,
            'iaas_virtual_machine_id'   => $this->model->id,
        ];

        // Call the StatService to create the statistics entry
        StatService::create($object, $params);

        // Log the update event
        Log::info('[EventHandlers\VirtualMachines\OnVirtualMachineUpdate] Virtual machine state updated: ' . $this->model->id);
    }
}