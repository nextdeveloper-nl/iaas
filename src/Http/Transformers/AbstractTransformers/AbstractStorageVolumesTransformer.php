<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class StorageVolumesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractStorageVolumesTransformer extends AbstractTransformer
{

    /**
     * @param StorageVolumes $model
     *
     * @return array
     */
    public function transform(StorageVolumes $model)
    {
                        $iaasStoragePoolId = \NextDeveloper\IAAS\Database\Models\StoragePools::where('id', $model->iaas_storage_pool_id)->first();
                    $iaasStorageMemberId = \NextDeveloper\IAAS\Database\Models\StorageMembers::where('id', $model->iaas_storage_member_id)->first();
            
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'name'  =>  $model->name,
            'disk_physical_type'  =>  $model->disk_physical_type,
            'connection_type'  =>  $model->connection_type,
            'connection_parameters'  =>  $model->connection_parameters,
            'total_hdd'  =>  $model->total_hdd,
            'used_hdd'  =>  $model->used_hdd,
            'free_hdd'  =>  $model->free_hdd,
            'virtual_allocation'  =>  $model->virtual_allocation,
            'is_storage'  =>  $model->is_storage == 1 ? true : false,
            'is_repo'  =>  $model->is_repo == 1 ? true : false,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'iaas_storage_pool_id'  =>  $iaasStoragePoolId ? $iaasStoragePoolId->uuid : null,
            'iaas_storage_member_id'  =>  $iaasStorageMemberId ? $iaasStorageMemberId->uuid : null,
            'is_alive'  =>  $model->is_alive == 1 ? true : false,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n













}
