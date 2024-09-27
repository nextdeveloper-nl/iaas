<?php

namespace NextDeveloper\IAAS\EventHandlers\Storages;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStats;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StatService;

/**
 * Class OnStorageMemberUpdate
 *
 * This event handler is triggered when a storage member is updated. It updates the storage member's statistics
 * and logs the update event.
 *
 * @package NextDeveloper\IAAS\EventHandlers\Storages
 */
class OnStorageMemberUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The storage member model instance.
     *
     * @var StorageMembers
     */
    private $model;

    /**
     * OnStorageMemberUpdate constructor.
     *
     * @param StorageMembers $members The storage member model instance.
     */
    public function __construct(StorageMembers $members)
    {
        $this->model = $members;
    }

    /**
     * Handles the storage member update event.
     *
     * This function performs the following steps:
     * 1. Defines the object type for storage member statistics.
     * 2. Prepares the parameters for the statistics update.
     * 3. Calls the StatService to create the statistics entry.
     * 4. Logs the update event.
     */
    public function handle(): void
    {
        // Define the object type for storage member statistics
        $object = 'NextDeveloper\IAAS\Database\Models\StorageMemberStats';

        // Prepare the parameters for the statistics update
        $params = [
            'used_disk'                 => $this->model->used_disk,
            'iaas_storage_member_id'    => $this->model->id,
        ];

        // Call the StatService to create the statistics entry
        StatService::create($object, $params);

        // Log the update event
        Log::info('[EventHandlers\StorageMembers\OnStorageMemberUpdate] Storage member state updated: ' . $this->model->id);
    }
}