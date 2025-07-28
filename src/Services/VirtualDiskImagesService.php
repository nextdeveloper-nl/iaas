<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Resize;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotContinueException;
use NextDeveloper\IAAS\Exceptions\CannotCreateDisk;
use NextDeveloper\IAAS\Exceptions\CannotCreateRootDisk;
use NextDeveloper\IAAS\Exceptions\CannotUpdateResourcesException;
use NextDeveloper\IAAS\Helpers\ResourceCalculationHelper;
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
            if($data['size'] <= 2000)
                $data['size'] = $data['size'] * 1024 * 1024 * 1024;
        }

        $vm = null;

        if(Str::isUuid($data['iaas_virtual_machine_id']))
            $vm = VirtualMachines::where('uuid', $data['iaas_virtual_machine_id'])->first();
        else
            $vm = VirtualMachines::where('id', $data['iaas_virtual_machine_id'])->first();

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

        if($computePool->pool_type != 'one' && $isRootDisk && !array_key_exists('iaas_storage_pool_id', $data)) {
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

    public static function update($id, array $data)
    {
        $vdi = null;

        if(Str::isUuid($id))
            $vdi = VirtualDiskImages::where('uuid', $id)->first();
        else
            $vdi = VirtualDiskImages::where('id', $id)->first();

        $requestedDiskSize = ResourceCalculationHelper::getDiskInBytes($data['size']);
        $shouldResizeDisk = false;

        if($vdi->size != $requestedDiskSize) {
            $availableDiskSizes = ResourceCalculationHelper::getAvailableDiskSizes(
                self::getComputePool($vdi)
            );

            if(!in_array($requestedDiskSize, $availableDiskSizes)) {
                if(VirtualMachinesService::isRunning(
                    self::getVirtualMachine($vdi)
                )) {
                    throw new CannotUpdateResourcesException('We cannot update the disk size, because the ' .
                        'server is running at the moment. Please shutdown your server and try again.');
                }
            }

            $shouldResizeDisk = true;
        }

        $data['size']   =   $requestedDiskSize;

        $vdi = parent::update($id, $data);

        if($shouldResizeDisk)
            dispatch(new Resize($vdi));

        return $vdi;
    }

    public static function getStorageVolume(VirtualDiskImages $vdi): ?StorageVolumes
    {
        return StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vdi->iaas_storage_volume_id)
            ->first();
    }

    public static function getStorageMember(VirtualDiskImages $vdi): ?StorageMembers
    {
        return StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', self::getStorageVolume($vdi)->iaas_storage_member_id)
            ->first();
    }

    public static function getStoragePool(VirtualDiskImages $vdi): ?StoragePools
    {
        return StoragePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vdi->iaas_storage_pool_id)
            ->first();
    }

    /**
     * Returns true if the storage pool is local.
     *
     * @param VirtualDiskImages $vdi
     * @return bool
     */
    public static function isOnLocalStorage(VirtualDiskImages $vdi) : bool
    {
        return self::getStoragePool($vdi)->storage_pool_type == 'local';
    }

    public static function isOnLeoOne(VirtualDiskImages $vdi): ?bool
    {
        $vm = self::getVirtualMachine($vdi);

        if(!$vm) return null;

        $cp = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_pool_id)
            ->first();

        return $cp->pool_type == 'one';
    }

    public static function getComputeMember(VirtualDiskImages $vdi) : ?ComputeMembers
    {
        $vm = self::getVirtualMachine($vdi);

        if(!$vm) return null;

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();
    }

    public static function getComputePool(VirtualDiskImages $vdi) : ?ComputePools
    {
        $vm = self::getVirtualMachine($vdi);

        if(!$vm) return null;

        return ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_pool_id)
            ->first();
    }

    public static function getVirtualMachine(VirtualDiskImages $vdi) : ?VirtualMachines
    {
        return VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vdi->iaas_virtual_machine_id)
            ->first();
    }

    public static function fix(VirtualDiskImages $vdi) : VirtualDiskImages
    {
        // Check if the storage pool is set to null
        if(!$vdi->iaas_storage_pool_id) {
            // Get the storage pool from the storage volume
            $storageVolume = self::getStorageVolume($vdi->fresh());

            if($storageVolume) {
                // Fixing the storage volume just to make sure it is correct
                $storageVolume = StorageVolumesService::fix($storageVolume);

                if($storageVolume->iaas_storage_pool_id) {
                    $vdi->updateQuietly([
                        'iaas_storage_pool_id' => $storageVolume->iaas_storage_pool_id,
                    ]);

                    return $vdi;
                }
            } else {
                throw new CannotContinueException('Cannot find the storage volume for this VDI. You need to check this out or fix this problem in the database.', '1');
            }
        }

        return $vdi->fresh();
    }
}
