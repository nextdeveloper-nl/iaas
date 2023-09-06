<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\IaasComputePool;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class IaasComputePoolTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractIaasComputePoolTransformer extends AbstractTransformer {

    /**
     * @param IaasComputePool $model
     *
     * @return array
     */
    public function transform(IaasComputePool $model) {
                        $iaasDatacenterId = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::where('id', $model->iaas_datacenter_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\IamAccount::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\IamUser::where('id', $model->iam_user_id)->first();
            
        return $this->buildPayload([
'id'  =>  $model->uuid,
'name'  =>  $model->name,
'resource_validator'  =>  $model->resource_validator,
'pool_type'  =>  $model->pool_type,
'pool_data'  =>  $model->pool_data,
'management_type'  =>  $model->management_type,
'virtualization'  =>  $model->virtualization,
'virtualization_version'  =>  $model->virtualization_version,
'provisioning_alg'  =>  $model->provisioning_alg,
'management_package_name'  =>  $model->management_package_name,
'is_active'  =>  $model->is_active,
'is_alive'  =>  $model->is_alive,
'is_public'  =>  $model->is_public,
'iaas_datacenter_id'  =>  $iaasDatacenterId ? $iaasDatacenterId->uuid : null,
'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
'created_at'  =>  $model->created_at,
'updated_at'  =>  $model->updated_at,
'deleted_at'  =>  $model->deleted_at,
    ]);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
