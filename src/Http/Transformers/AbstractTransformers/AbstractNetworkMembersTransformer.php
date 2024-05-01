<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class NetworkMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractNetworkMembersTransformer extends AbstractTransformer
{

    /**
     * @param NetworkMembers $model
     *
     * @return array
     */
    public function transform(NetworkMembers $model)
    {
                        $iaasNetworkPoolId = \NextDeveloper\IAAS\Database\Models\NetworkPools::where('id', $model->iaas_network_pool_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'ip_addr'  =>  $model->ip_addr,
            'ssh_username'  =>  $model->ssh_username,
            'ssh_password'  =>  $model->ssh_password,
            'iaas_network_pool_id'  =>  $iaasNetworkPoolId ? $iaasNetworkPoolId->uuid : null,
            'tags'  =>  $model->tags,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
