<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class NetworkPoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractNetworkPoolsTransformer extends AbstractTransformer
{

    /**
     * @param NetworkPools $model
     *
     * @return array
     */
    public function transform(NetworkPools $model)
    {
                        $iaasDatacenterId = \NextDeveloper\IAAS\Database\Models\Datacenters::where('id', $model->iaas_datacenter_id)->first();
                    $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('id', $model->iaas_cloud_node_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
                    $commonCurrencyId = \NextDeveloper\Commons\Database\Models\Currencies::where('id', $model->common_currency_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'vlan_start'  =>  $model->vlan_start,
            'vlan_end'  =>  $model->vlan_end,
            'vxlan_start'  =>  $model->vxlan_start,
            'vxlan_end'  =>  $model->vxlan_end,
            'is_vlan_available'  =>  $model->is_vlan_available,
            'is_vxlan_available'  =>  $model->is_vxlan_available,
            'is_active'  =>  $model->is_active,
            'iaas_datacenter_id'  =>  $iaasDatacenterId ? $iaasDatacenterId->uuid : null,
            'iaas_cloud_node_id'  =>  $iaasCloudNodeId ? $iaasCloudNodeId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'provisioning_alg'  =>  $model->provisioning_alg,
            'resource_validator'  =>  $model->resource_validator,
            'tags'  =>  $model->tags,
            'price_pergb'  =>  $model->price_pergb,
            'common_currency_id'  =>  $commonCurrencyId ? $commonCurrencyId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE





















}