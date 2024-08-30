<?php

namespace NextDeveloper\IAAS\EventHandlers\ComputeMembers;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStats;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StatService;

/**
 * Class OnComputeMemberUpdate
 *
 * This event handler is triggered when a compute member is updated. It updates the compute member's statistics
 * and logs the update event.
 *
 * @package NextDeveloper\IAAS\EventHandlers\ComputeMembers
 */
class OnComputeMemberUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The compute member model instance.
     *
     * @var ComputeMembers
     */
    private $model;

    /**
     * OnComputeMemberUpdate constructor.
     *
     * @param ComputeMembers $members The compute member model instance.
     */
    public function __construct(ComputeMembers $members)
    {
        $this->model = $members;
    }

    /**
     * Handles the compute member update event.
     *
     * This function performs the following steps:
     * 1. Define the object type for compute member statistics.
     * 2. Prepares the parameters for the statistics update.
     * 3. Call the StatService to create the statistics entry.
     * 4. Log the update event.
     */
    public function handle(): void
    {
        // Define the object type for compute member statistics
        $object = 'NextDeveloper\IAAS\Database\Models\ComputeMemberStats';

        // Prepare the parameters for the statistics update
        $params = [
            'used_ram'                  => $this->model->used_ram,
            'used_cpu'                  => $this->model->used_cpu,
            'running_vm'                => $this->model->running_vm,
            'iaas_compute_member_id'    => $this->model->id,
        ];

        // Call the StatService to create the statistics entry
        StatService::create($object, $params);

        // Log the update event
        Log::info('[EventHandlers\ComputeMembers\OnComputeMemberUpdate] Compute member state updated: ' . $this->model->id);
    }
}