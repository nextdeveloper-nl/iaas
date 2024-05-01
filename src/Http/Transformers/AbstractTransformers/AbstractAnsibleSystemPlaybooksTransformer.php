<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybooks;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class AnsibleSystemPlaybooksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsibleSystemPlaybooksTransformer extends AbstractTransformer
{

    /**
     * @param AnsibleSystemPlaybooks $model
     *
     * @return array
     */
    public function transform(AnsibleSystemPlaybooks $model)
    {
                        $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $ansibleServerId = \NextDeveloper\\Database\Models\AnsibleServers::where('id', $model->ansible_server_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'slug'  =>  $model->slug,
            'name'  =>  $model->name,
            'description'  =>  $model->description,
            'package'  =>  $model->package,
            'path'  =>  $model->path,
            'playbook_filename'  =>  $model->playbook_filename,
            'is_public'  =>  $model->is_public,
            'is_procedure'  =>  $model->is_procedure,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'ansible_server_id'  =>  $ansibleServerId ? $ansibleServerId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
