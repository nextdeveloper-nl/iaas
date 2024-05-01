<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class DhcpServersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractDhcpServersTransformer extends AbstractTransformer
{

    /**
     * @param DhcpServers $model
     *
     * @return array
     */
    public function transform(DhcpServers $model)
    {
                        $iaasVirtualMachineId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machine_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'iaas_virtual_machine_id'  =>  $iaasVirtualMachineId ? $iaasVirtualMachineId->uuid : null,
            'dhcp_data'  =>  $model->dhcp_data,
            'is_public'  =>  $model->is_public,
            'ssh_username'  =>  $model->ssh_username,
            'ssh_password'  =>  $model->ssh_password,
            'ip_addr'  =>  $model->ip_addr,
            'api_token'  =>  $model->api_token,
            'api_url'  =>  $model->api_url,
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
