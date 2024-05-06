<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class VirtualNetworkCardsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVirtualNetworkCardsTransformer extends AbstractTransformer
{

    /**
     * @param VirtualNetworkCards $model
     *
     * @return array
     */
    public function transform(VirtualNetworkCards $model)
    {
                        $iaasNetworkId = \NextDeveloper\IAAS\Database\Models\Networks::where('id', $model->iaas_network_id)->first();
                    $iaasVirtualMachineId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machine_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'mac_addr'  =>  $model->mac_addr,
            'bandwidth_limit'  =>  $model->bandwidth_limit,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'iaas_network_id'  =>  $iaasNetworkId ? $iaasNetworkId->uuid : null,
            'iaas_virtual_machine_id'  =>  $iaasVirtualMachineId ? $iaasVirtualMachineId->uuid : null,
            'device_number'  =>  $model->device_number,
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
