<?php

namespace NextDeveloper\IAAS\EventHandlers\Storages;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStats;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StatService;

/**
 * Class OnStorageVolumeUpdate
 *
 * This event handler is triggered when a storage volume is updated. It updates the storage volume's statistics
 * and logs the update event.
 *
 * @package NextDeveloper\IAAS\EventHandlers\Storages
 */
class OnStorageVolumeUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The storage volume model instance.
     *
     * @var StorageVolumes
     */
    private $model;

    /**
     * OnStorageVolumeUpdate constructor.
     *
     * @param StorageVolumes $volumes The storage volume model instance.
     */
    public function __construct(StorageVolumes $volumes)
    {
        $this->model = $volumes;
    }

    /**
     * Handles the storage volume update event.
     *
     * This function performs the following steps:
     * 1. Defines the object type for storage volume statistics.
     * 2. Prepares the parameters for the statistics update.
     * 3. Calls the StatService to create the statistics entry.
     * 4. Logs the update event.
     */
    public function handle(): void
    {
        // Define the object type for storage volume statistics
        $object = 'NextDeveloper\IAAS\Database\Models\StorageVolumeStats';

        // Prepare the parameters for the statistics update
        $params = [
            'used_disk'                 => $this->model->used_hdd,
            'free_disk'                 => $this->model->free_hdd,
            'iaas_storage_volume_id'    => $this->model->id,
        ];

        // Call the StatService to create the statistics entry
        StatService::create($object, $params);

        // Log the update event
        Log::info('[EventHandlers\StorageVolumes\OnStorageVolumeUpdate] Storage volume state updated: ' . $this->model->id);
    }
}