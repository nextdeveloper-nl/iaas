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
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StorageMembersService;
use NextDeveloper\IAAS\Services\StoragePoolsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use PlusClouds\IAAS\Services\XenServer\XenServerService;

class StorageMemberXenService extends AbstractXenService
{
    public static function createStorageMemberFromSharedVolume(ComputeMemberStorageVolumes $volume) : StorageMembers
    {
        $storageMember = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('local_ip_addr', $volume->block_device_data['device-config']['server'])
            ->first();

        if($storageMember)
            return $storageMember;

        $storageMember = StorageMembersService::create([
            'name'  => 'Storage Member with IP: ' . $volume->block_device_data['device-config']['server'],
            'hostname'  =>  'unknown',
            'ip_addr'   =>  $volume->block_device_data['device-config']['server'],
            'local_ip_addr' =>  $volume->block_device_data['device-config']['server'],
            'iam_account_id'    =>  $volume->iam_account_id,
            'iam_user_id'       =>  $volume->iam_user_id
        ]);

        return $storageMember;
    }

    public static function createStorageMemberFromComputeMember(ComputeMembers $computeMember) : StorageMembers {
        $storageMember = StorageMembersService::getStorageMemberOfComputeMember($computeMember);

        if($storageMember)
            return $storageMember;

        //  We need to create a new storage member but first we need to check if we have a local storage pool
        $cloudNode = ComputeMembersService::getCloudNode($computeMember);

        $storagePool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $cloudNode->id)
            ->where('storage_pool_type', 'local')
            ->first();

        //  Creating the storage pool if not exists
        if(!$storagePool) {
            $storagePool = StoragePoolsService::create([
                'name'  => 'Local Storage Pool',
                'storage_pool_type'  => 'local',
                'iaas_cloud_node_id'    => $cloudNode->id,
            ]);
        }

        //  Now we can create storage member
        $storageMember = StorageMembersService::create([
            'name'  => $computeMember->name . ' Local Storage',
            'hostname'  =>  $computeMember->hostname,
            'ip_addr'   =>  $computeMember->ip_addr,
            'local_ip_addr' => $computeMember->local_ip_addr,
            'iaas_storage_pool_id'   => $storagePool->id,
            'iam_account_id'    =>  $computeMember->iam_account_id,
            'iam_user_id'   =>  $computeMember->iam_user_id
        ]);

        return $storageMember;
    }
}
