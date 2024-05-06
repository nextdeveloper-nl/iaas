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
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'name'  =>  $model->name,
            'disk_physical_type'  =>  $model->disk_physical_type,
            'connection_parameters'  =>  $model->connection_parameters,
            'total_hdd'  =>  $model->total_hdd,
            'used_hdd'  =>  $model->used_hdd,
            'free_hdd'  =>  $model->free_hdd,
            'virtual_allocation'  =>  $model->virtual_allocation,
            'is_storage'  =>  $model->is_storage,
            'is_repo'  =>  $model->is_repo,
            'is_cdrom'  =>  $model->is_cdrom,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'iaas_storage_pool_id'  =>  $iaasStoragePoolId ? $iaasStoragePoolId->uuid : null,
            'iaas_storage_member_id'  =>  $iaasStorageMemberId ? $iaasStorageMemberId->uuid : null,
            'is_alive'  =>  $model->is_alive,
            'tags'  =>  $model->tags,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE






















}
