<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\NetworkMemberDevices;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class NetworkMemberDevicesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractNetworkMemberDevicesTransformer extends AbstractTransformer
{

    /**
     * @param NetworkMemberDevices $model
     *
     * @return array
     */
    public function transform(NetworkMemberDevices $model)
    {
                        $iaasNetworkMemberId = \NextDeveloper\IAAS\Database\Models\NetworkMembers::where('id', $model->iaas_network_member_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'device_identification'  =>  $model->device_identification,
            'is_healthy'  =>  $model->is_healthy,
            'health_information'  =>  $model->health_information,
            'device_type'  =>  $model->device_type,
            'iaas_network_member_id'  =>  $iaasNetworkMemberId ? $iaasNetworkMemberId->uuid : null,
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
