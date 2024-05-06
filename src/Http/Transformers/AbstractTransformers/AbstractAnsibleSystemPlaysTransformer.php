<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlays;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class AnsibleSystemPlaysTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsibleSystemPlaysTransformer extends AbstractTransformer
{

    /**
     * @param AnsibleSystemPlays $model
     *
     * @return array
     */
    public function transform(AnsibleSystemPlays $model)
    {
                        $iaasAnsibleSystemPlaybookId = \NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybooks::where('id', $model->iaas_ansible_system_playbook_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'iaas_ansible_system_playbook_id'  =>  $iaasAnsibleSystemPlaybookId ? $iaasAnsibleSystemPlaybookId->uuid : null,
            'hosts'  =>  $model->hosts,
            'roles'  =>  $model->roles,
            'config'  =>  $model->config,
            'become'  =>  $model->become,
            'gather_facts'  =>  $model->gather_facts,
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
