<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\IaasNetworkPool;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class IaasNetworkPoolTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractIaasNetworkPoolTransformer extends AbstractTransformer {

    /**
     * @param IaasNetworkPool $model
     *
     * @return array
     */
    public function transform(IaasNetworkPool $model) {
                        $iaasDatacenterId = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::where('id', $model->iaas_datacenter_id)->first();
                    $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::where('id', $model->iaas_cloud_node_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\IamAccount::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\IamUser::where('id', $model->iam_user_id)->first();
            
        return $this->buildPayload([
'id'  =>  $model->uuid,
'name'  =>  $model->name,
'vlan_start'  =>  $model->vlan_start,
'vlan_end'  =>  $model->vlan_end,
'vxlan_start'  =>  $model->vxlan_start,
'vxlan_end'  =>  $model->vxlan_end,
'has_vlan_support'  =>  $model->has_vlan_support,
'has_vxlan_support'  =>  $model->has_vxlan_support,
'is_active'  =>  $model->is_active,
'iaas_datacenter_id'  =>  $iaasDatacenterId ? $iaasDatacenterId->uuid : null,
'iaas_cloud_node_id'  =>  $iaasCloudNodeId ? $iaasCloudNodeId->uuid : null,
'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
'provisioning_alg'  =>  $model->provisioning_alg,
'management_package_name'  =>  $model->management_package_name,
'resource_validator'  =>  $model->resource_validator,
'created_at'  =>  $model->created_at,
'updated_at'  =>  $model->updated_at,
'deleted_at'  =>  $model->deleted_at,
    ]);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
