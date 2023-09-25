<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class ComputeMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractComputeMembersTransformer extends AbstractTransformer
{

    /**
     * @param ComputeMembers $model
     *
     * @return array
     */
    public function transform(ComputeMembers $model)
    {
                        $iaasComputePoolId = \NextDeveloper\IAAS\Database\Models\ComputePools::where('id', $model->iaas_compute_pool_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
            
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'hostname'  =>  $model->hostname,
            'ip_addr'  =>  $model->ip_addr,
            'local_ip_addr'  =>  $model->local_ip_addr,
            'api_url'  =>  $model->api_url,
            'port'  =>  $model->port,
            'username'  =>  $model->username,
            'password'  =>  $model->password,
            'features'  =>  $model->features,
            'is_behind_firewall'  =>  $model->is_behind_firewall,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'total_cpu'  =>  $model->total_cpu,
            'total_ram'  =>  $model->total_ram,
            'used_cpu'  =>  $model->used_cpu,
            'used_ram'  =>  $model->used_ram,
            'free_cpu'  =>  $model->free_cpu,
            'free_ram'  =>  $model->free_ram,
            'total_vm'  =>  $model->total_vm,
            'overbooking_ratio'  =>  $model->overbooking_ratio,
            'max_overbooking_ratio'  =>  $model->max_overbooking_ratio,
            'cpu_info'  =>  $model->cpu_info,
            'uptime'  =>  $model->uptime,
            'idle_time'  =>  $model->idle_time,
            'benchmark_score'  =>  $model->benchmark_score,
            'is_maintenance'  =>  $model->is_maintenance == 1 ? true : false,
            'is_alive'  =>  $model->is_alive,
            'iaas_compute_pool_id'  =>  $iaasComputePoolId ? $iaasComputePoolId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n


















}
