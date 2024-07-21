<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\StatesService;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMetrics;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotCreateDisk;
use NextDeveloper\IAAS\Exceptions\CannotCreateRootDisk;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualDiskImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for VirtualDiskImages
 *
 * Class VirtualDiskImagesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualDiskImagesService extends AbstractVirtualDiskImagesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create($data) {
        if(array_key_exists('size', $data)) {
            $data['size'] = $data['size'] * 1024 * 1024 * 1024;
        }

        $vm = VirtualMachines::where('uuid', $data['iaas_virtual_machine_id'])->first();
        $computePool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_pool_id)
            ->first();

        $isRootDisk = false;

        $vdis = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)
            ->get();

        if($vdis->count() == 0) {
            $isRootDisk = true;
        }

        if(!$isRootDisk && !array_key_exists('iaas_storage_pool_id', $data)) {
            throw new CannotCreateDisk('We cannot create this disk because this is not a root ' .
                'disk and we dont know the storage pool. Please provide a storage pool so that ' .
                'I can create this disk.', '1');
        }

        if($computePool->pool_type != 'one' && $isRootDisk) {
            throw new CannotCreateRootDisk('I cannot create root disk for this server because this is not a ' .
                'one pool type. You need to select a storage pool like SSD Storage, SAS Storage or ' .
                'NVMe Storage pool, ' .
                'for me to deploy the disk.', '2');
        }

        //  Here if the disk is root disk (first disk) and the pool is one, we need to check if the storage pool is local
        //  If not local we will fix this to local storage pool
        if($computePool->pool_type == 'one' && $isRootDisk) {
            $localStoragePool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
                ->where('storage_pool_type', 'local')
                ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                ->first();

            if(array_key_exists('iaas_storage_pool_id', $data))
                $currentStoragePool = $data['iaas_storage_pool_id'];
            else
                $currentStoragePool = 'Not set before ¯\_(ツ)_/¯ (What can I do sometimes ?)';

            StateHelper::setState($vm, 'storage_pool_change', 'Local storage pool ' .
                'changed from ' . $currentStoragePool . ' to ' . $localStoragePool->uuid, 'info');

            $data['iaas_storage_pool_id'] = $localStoragePool->uuid;
            $data['device_number'] =   0;
        }

        if($computePool->pool_type == 'one' && array_key_exists('iaas_storage_pool_id', $data) && !$isRootDisk) {
            $storagePool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
                ->where('uuid', $data['iaas_storage_pool_id'])
                ->first();

            if($storagePool->storage_pool_type == 'local') {
                throw new CannotCreateRootDisk('We cannot create non-root disk on local storage pool' .
                    ' for this compute pool type. You need to select another storage pool like; SSD Storage, ' .
                    'NVMe Storage or SAS Storage pools.', '3');
            }

            //  Here count will return +1 of what we want, so we leave it at is.
            $data['device_number'] = $vdis->count();
        }

        return parent::create($data);
    }
}
