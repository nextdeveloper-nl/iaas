<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Http\Transformers\MediaTransformer;
use NextDeveloper\Commons\Database\Models\AvailableActions;
use NextDeveloper\Commons\Http\Transformers\AvailableActionsTransformer;
use NextDeveloper\Commons\Database\Models\States;
use NextDeveloper\Commons\Http\Transformers\StatesTransformer;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class ComputeMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractComputeMembersTransformer extends AbstractTransformer
{

    /**
     * @var array
     */
    protected array $availableIncludes = [
        'states',
        'actions',
        'media'
    ];

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
            'management_data'  =>  $model->management_data,
            'features'  =>  $model->features,
            'is_behind_firewall'  =>  $model->is_behind_firewall,
            'is_management_agent_available'  =>  $model->is_management_agent_available,
            'ssh_username'  =>  $model->ssh_username,
            'ssh_password'  =>  $model->ssh_password,
            'ssh_port'  =>  $model->ssh_port,
            'hypervisor_uuid'  =>  $model->hypervisor_uuid,
            'hypervisor_data'  =>  $model->hypervisor_data,
            'hypervisor_model'  =>  $model->hypervisor_model,
            'has_warning'  =>  $model->has_warning,
            'has_error'  =>  $model->has_error,
            'total_socket'  =>  $model->total_socket,
            'total_cpu'  =>  $model->total_cpu,
            'total_ram'  =>  $model->total_ram,
            'used_cpu'  =>  $model->used_cpu,
            'used_ram'  =>  $model->used_ram,
            'running_vm'  =>  $model->running_vm,
            'halted_vm'  =>  $model->halted_vm,
            'total_vm'  =>  $model->total_vm,
            'max_overbooking_ratio'  =>  $model->max_overbooking_ratio,
            'cpu_info'  =>  $model->cpu_info,
            'uptime'  =>  $model->uptime,
            'idle_time'  =>  $model->idle_time,
            'benchmark_score'  =>  $model->benchmark_score,
            'is_in_maintenance'  =>  $model->is_in_maintenance,
            'is_alive'  =>  $model->is_alive,
            'iaas_compute_pool_id'  =>  $iaasComputePoolId ? $iaasComputePoolId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'tags'  =>  $model->tags,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    public function includeStates(ComputeMembers $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(ComputeMembers $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(Datacenters $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
