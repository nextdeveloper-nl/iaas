<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class ComputeMemberStorageVolumesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractComputeMemberStorageVolumesTransformer extends AbstractTransformer
{

    /**
     * @param ComputeMemberStorageVolumes $model
     *
     * @return array
     */
    public function transform(ComputeMemberStorageVolumes $model)
    {
            
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
