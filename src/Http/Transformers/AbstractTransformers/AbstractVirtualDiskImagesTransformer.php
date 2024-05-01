<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class VirtualDiskImagesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVirtualDiskImagesTransformer extends AbstractTransformer
{

    /**
     * @param VirtualDiskImages $model
     *
     * @return array
     */
    public function transform(VirtualDiskImages $model)
    {
                        $iaasStorageVolumeId = \NextDeveloper\IAAS\Database\Models\StorageVolumes::where('id', $model->iaas_storage_volume_id)->first();
                    $iaasVirtualMachineId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machine_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'size'  =>  $model->size,
            'physical_utilization'  =>  $model->physical_utilization,
            'available_operations'  =>  $model->available_operations,
            'current_operations'  =>  $model->current_operations,
            'is_cdrom'  =>  $model->is_cdrom,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'iaas_storage_volume_id'  =>  $iaasStorageVolumeId ? $iaasStorageVolumeId->uuid : null,
            'iaas_virtual_machine_id'  =>  $iaasVirtualMachineId ? $iaasVirtualMachineId->uuid : null,
            'device_number'  =>  $model->device_number,
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
