<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookAnsibleRoles;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class AnsiblePlaybookAnsibleRolesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsiblePlaybookAnsibleRolesTransformer extends AbstractTransformer
{

    /**
     * @param AnsiblePlaybookAnsibleRoles $model
     *
     * @return array
     */
    public function transform(AnsiblePlaybookAnsibleRoles $model)
    {
                        $iaasAnsibleServerId = \NextDeveloper\IAAS\Database\Models\AnsibleServers::where('id', $model->iaas_ansible_server_id)->first();
                    $iaasAnsiblePlaybookId = \NextDeveloper\IAAS\Database\Models\AnsiblePlaybooks::where('id', $model->iaas_ansible_playbook_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'position'  =>  $model->position,
            'config'  =>  $model->config,
            'iaas_ansible_server_id'  =>  $iaasAnsibleServerId ? $iaasAnsibleServerId->uuid : null,
            'iaas_ansible_playbook_id'  =>  $iaasAnsiblePlaybookId ? $iaasAnsiblePlaybookId->uuid : null,
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
