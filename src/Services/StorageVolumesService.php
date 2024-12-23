<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
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
}
