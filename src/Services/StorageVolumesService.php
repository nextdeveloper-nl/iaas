<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractStorageVolumesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for StorageVolumes
 *
 * Class StorageVolumesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class StorageVolumesService extends AbstractStorageVolumesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getStorageMember(StorageVolumes $volume) : ?StorageMembers
    {
        return StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $volume->iaas_storage_member_id)
            ->first();
    }

    public static function getVolumeByUuid($uuid) : ?StorageVolumes
    {
        return StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $uuid)
            ->first();
    }

    public static function fix(StorageVolumes $volume) : StorageVolumes
    {
        //  Check if the storage volume does not have storage pool
        if(!$volume->iaas_storage_pool_id) {
            //  Here we should try to understand if there are any other storage volumes with the same name exists
            $storageVolumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('name', $volume->name)
                ->get();

            foreach ($storageVolumes as $storageVolume) {
                if($storageVolume->iaas_storage_pool_id != null) {
                    $volume->updateQuietly([
                        'iaas_storage_pool_id' => $storageVolume->iaas_storage_pool_id,
                    ]);

                    break;
                }
            }
        }

        return $volume->fresh();
    }
}
