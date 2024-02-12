<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class VirtualMachinesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVirtualMachinesTransformer extends AbstractTransformer
{

    /**
     * @param VirtualMachines $model
     *
     * @return array
     */
    public function transform(VirtualMachines $model)
    {
                        $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('id', $model->iaas_cloud_node_id)->first();
                    $iaasComputeMemberId = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('id', $model->iaas_compute_member_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
                    $iaasVirtualMachinesId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machines_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'username'  =>  $model->username,
            'password'  =>  $model->password,
            'hostname'  =>  $model->hostname,
            'description'  =>  $model->description,
            'os'  =>  $model->os,
            'distro'  =>  $model->distro,
            'version'  =>  $model->version,
            'domain_type'  =>  $model->domain_type,
            'status'  =>  $model->status,
            'cpu'  =>  $model->cpu,
            'ram'  =>  $model->ram,
            'winrm_enabled'  =>  $model->winrm_enabled,
            'available_operations'  =>  $model->available_operations,
            'current_operations'  =>  $model->current_operations,
            'blocked_operations'  =>  $model->blocked_operations,
            'console_data'  =>  $model->console_data,
            'is_snapshot'  =>  $model->is_snapshot,
            'is_lost'  =>  $model->is_lost,
            'is_locked'  =>  $model->is_locked,
            'last_metadata_request'  =>  $model->last_metadata_request,
            'features'  =>  $model->features,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'iaas_cloud_node_id'  =>  $iaasCloudNodeId ? $iaasCloudNodeId->uuid : null,
            'iaas_compute_member_id'  =>  $iaasComputeMemberId ? $iaasComputeMemberId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'iaas_virtual_machines_id'  =>  $iaasVirtualMachinesId ? $iaasVirtualMachinesId->uuid : null,
            'tags'  =>  $model->tags,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











}
