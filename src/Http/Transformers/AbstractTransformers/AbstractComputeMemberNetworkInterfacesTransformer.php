<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class ComputeMemberNetworkInterfacesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractComputeMemberNetworkInterfacesTransformer extends AbstractTransformer
{

    /**
     * @param ComputeMemberNetworkInterfaces $model
     *
     * @return array
     */
    public function transform(ComputeMemberNetworkInterfaces $model)
    {
                        $iaasComputeMemberId = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('id', $model->iaas_compute_member_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'device'  =>  $model->device,
            'mac_addr'  =>  $model->mac_addr,
            'vlan'  =>  $model->vlan,
            'mtu'  =>  $model->mtu,
            'is_management'  =>  $model->is_management,
            'is_default'  =>  $model->is_default,
            'is_connected'  =>  $model->is_connected,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'iaas_compute_member_id'  =>  $iaasComputeMemberId ? $iaasComputeMemberId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            'is_bridge'  =>  $model->is_bridge,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}
