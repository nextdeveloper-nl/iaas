<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookExecutions;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class AnsiblePlaybookExecutionsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsiblePlaybookExecutionsTransformer extends AbstractTransformer
{

    /**
     * @param AnsiblePlaybookExecutions $model
     *
     * @return array
     */
    public function transform(AnsiblePlaybookExecutions $model)
    {
                        $iaasVirtualMachineId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machine_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'is_external_machine'  =>  $model->is_external_machine,
            'iaas_virtual_machine_id'  =>  $iaasVirtualMachineId ? $iaasVirtualMachineId->uuid : null,
            'ssh_username'  =>  $model->ssh_username,
            'ssh_password'  =>  $model->ssh_password,
            'ssh_port'  =>  $model->ssh_port,
            'ip_v4'  =>  $model->ip_v4,
            'ip_v6'  =>  $model->ip_v6,
            'last_execution_time'  =>  $model->last_execution_time,
            'execution_total_time'  =>  $model->execution_total_time,
            'last_output'  =>  $model->last_output,
            'result_ok'  =>  $model->result_ok,
            'result_unreachable'  =>  $model->result_unreachable,
            'result_failed'  =>  $model->result_failed,
            'result_skipped'  =>  $model->result_skipped,
            'result_rescued'  =>  $model->result_rescued,
            'result_ignored'  =>  $model->result_ignored,
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
