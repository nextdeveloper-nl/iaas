<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StorageMembersService;
use NextDeveloper\IAAS\Services\StoragePoolsService;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use PlusClouds\IAAS\Services\XenServer\XenServerService;

class StorageVolumeXenService extends AbstractXenService
{
    public static function updateSharedStorage(ComputeMemberStorageVolumes $storageVolume) : StorageVolumes {
        /**
         * First we need to check if the storage member exists by looking at local_ip_addr and PBD information
         * in storage volume.
         */
        $blockDeviceData = $storageVolume->block_device_data;

        if(array_key_exists(0, $blockDeviceData))
            $blockDeviceData = $blockDeviceData[0];

        $storageMember = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('local_ip_addr', trim($blockDeviceData['device-config']['server']))
            ->first();

        if(!$storageMember)
            $storageMember = StorageMemberXenService::createStorageMemberFromSharedVolume($storageVolume);

        $storageMemberVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $storageVolume->hypervisor_uuid)
            ->first();

        $hypervisorData = $storageVolume->hypervisor_data;

        $data = [
            'name'              =>  $storageVolume->name,
            'iam_account_id'    =>  $storageVolume->iam_account_id,
            'iam_user_id'       =>  $storageVolume->iam_user_id,
            'hypervisor_uuid'   =>  $storageVolume->hypervisor_uuid,
            'total_hdd'         =>  ceil($hypervisorData['physical-size'] / 1000 / 1000 / 1000),
            'used_hdd'          =>  ceil($hypervisorData['physical-utilisation'] / 1000 / 1000 / 1000),
            'virtual_allocation' =>  ceil($hypervisorData['virtual-allocation'] / 1000 / 1000 / 1000),
            //  Free HDD is a generated column, that is why we are not calculating that.
            //'free_hdd'      =>  .....
            'iaas_storage_member_id'    =>  $storageMember->id,
            'iaas_storage_pool_id'      =>  $storageMember->iaas_storage_pool_id
        ];

        if(!$storageMemberVolume) {
            $storageMemberVolume = StorageVolumesService::create($data);
        } else {
            $storageMemberVolume->update($data);
        }

        //  This is here because just in case of missed this in the past!
        //  Since this is a complex scanning, it may happen, so we need to update the storage member id
        if(!$storageMemberVolume->iaas_storage_pool_id) {
            $storageMemberVolume->update([
                'iaas_storage_pool_id'  =>  $storageMember->iaas_storage_pool_id
            ]);
        }

        $storageVolume = $storageVolume->fresh();

        $storageVolume->update([
            'iaas_storage_volume_id'    =>  $storageMemberVolume->id,
            'iaas_storage_member_id'    =>  $storageMember->id,
            'iaas_storage_pool_id'      =>  $storageMember->iaas_storage_pool_id
        ]);

        return $storageMemberVolume;
    }
}
