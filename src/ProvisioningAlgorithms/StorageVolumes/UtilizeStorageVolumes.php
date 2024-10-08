<?php

namespace NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Exceptions\NotEnoughResourcesException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This algorithm tries to fill the most busy compute member first. The reason of this approach is most of the time
 * consume less electric in the datacenter or keep the other compute members as idle as possible, or even in standby
 * mode
 */
class UtilizeStorageVolumes extends AbstractStorageVolumeAlgorithm
{
    /**
     * This function will calculate the best compute member for the given resources
     *
     * @param integer $cpu
     * @param integer $ram
     * @return mixed
     */
    public function calculate(ComputeMembers $member, $size) : ? StorageVolumes
    {
        Log::info(__METHOD__ . ' | Calculating the volume needed for the compute member: ' . $member->name);

        /**
         * We need to find the perfect storage volume for this compute member. We will try to find the most busy
         * one first.
         *
         * If we cannot find any storage volume that can handle the given resources, we will return null and then the
         * caller will try to find another storage pool that can handle the given resources.
         */
        $storageVolumesMounted = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $member->id)
            ->where('iaas_storage_pool_id', $this->storagePools->id)
            ->pluck('iaas_storage_volume_id');

        Log::info(__METHOD__ . ' | Found volumes mounted: ', $storageVolumesMounted);

        /**
         * We didnt check the disk size here. We need to check the disk size here.
         */

        $storageVolumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->whereIn('id', $storageVolumesMounted)
            ->first();

        Log::info(__METHOD__ . ' | Volume we select: ', $storageVolumes);

        return $storageVolumes;
    }
}
