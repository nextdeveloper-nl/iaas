<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\AnsibleServers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class AnsibleServersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsibleServersTransformer extends AbstractTransformer
{

    /**
     * @param AnsibleServers $model
     *
     * @return array
     */
    public function transform(AnsibleServers $model)
    {
                        $iaasVirtualMachineId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machine_id)->first();
                    $commonCurrencyId = \NextDeveloper\Commons\Database\Models\Currencies::where('id', $model->common_currency_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'is_external_machine'  =>  $model->is_external_machine,
            'iaas_virtual_machine_id'  =>  $iaasVirtualMachineId ? $iaasVirtualMachineId->uuid : null,
            'ssh_username'  =>  $model->ssh_username,
            'ssh_password'  =>  $model->ssh_password,
            'ssh_port'  =>  $model->ssh_port,
            'ip_v4'  =>  $model->ip_v4,
            'ip_v6'  =>  $model->ip_v6,
            'ansible_version'  =>  $model->ansible_version,
            'roles_path'  =>  $model->roles_path,
            'system_playbooks_path'  =>  $model->system_playbooks_path,
            'execution_path'  =>  $model->execution_path,
            'is_active'  =>  $model->is_active,
            'is_public'  =>  $model->is_public,
            'price_persecond'  =>  $model->price_persecond,
            'common_currency_id'  =>  $commonCurrencyId ? $commonCurrencyId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}
