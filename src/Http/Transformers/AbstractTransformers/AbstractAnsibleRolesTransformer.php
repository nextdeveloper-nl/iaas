<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\AnsibleRoles;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class AnsibleRolesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsibleRolesTransformer extends AbstractTransformer
{

    /**
     * @param AnsibleRoles $model
     *
     * @return array
     */
    public function transform(AnsibleRoles $model)
    {
                        $iaasAnsibleServerId = \NextDeveloper\IAAS\Database\Models\AnsibleServers::where('id', $model->iaas_ansible_server_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'version'  =>  $model->version,
            'release_number'  =>  $model->release_number,
            'config'  =>  $model->config,
            'hash'  =>  $model->hash,
            'min_ansible_version'  =>  $model->min_ansible_version,
            'prerequisites'  =>  $model->prerequisites,
            'is_active'  =>  $model->is_active,
            'is_procedure'  =>  $model->is_procedure,
            'iaas_ansible_server_id'  =>  $iaasAnsibleServerId ? $iaasAnsibleServerId->uuid : null,
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