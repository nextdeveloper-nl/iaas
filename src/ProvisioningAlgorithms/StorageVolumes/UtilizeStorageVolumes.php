<?php

namespace NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes;

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

        $storageVolumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->whereIn('id', $storageVolumesMounted)
            ->get();

        dd($storageVolumes);

        throw new NotEnoughResourcesException('There is not enough resources to allocate this' .
            ' much ram (' . $ram . ' GB) in the pool: '
            . $this->computePool->name . '. You may want to try another pool that is not full.');
    }
}
