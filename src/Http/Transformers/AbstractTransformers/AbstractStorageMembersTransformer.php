<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class StorageMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractStorageMembersTransformer extends AbstractTransformer
{

    /**
     * @param StorageMembers $model
     *
     * @return array
     */
    public function transform(StorageMembers $model)
    {
                        $iaasStoragePoolId = \NextDeveloper\IAAS\Database\Models\StoragePools::where('id', $model->iaas_storage_pool_id)->first();
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
            'configuration_data'  =>  $model->configuration_data,
            'is_healthy'  =>  $model->is_healthy,
            'has_warning'  =>  $model->has_warning,
            'has_error'  =>  $model->has_error,
            'features'  =>  $model->features,
            'is_behind_firewall'  =>  $model->is_behind_firewall,
            'total_socket'  =>  $model->total_socket,
            'total_cpu'  =>  $model->total_cpu,
            'total_ram'  =>  $model->total_ram,
            'total_disk'  =>  $model->total_disk,
            'used_disk'  =>  $model->used_disk,
            'disk_info'  =>  $model->disk_info,
            'uptime'  =>  $model->uptime,
            'idle_time'  =>  $model->idle_time,
            'benchmark_score'  =>  $model->benchmark_score,
            'is_maintenance'  =>  $model->is_maintenance,
            'is_alive'  =>  $model->is_alive,
            'iaas_storage_pool_id'  =>  $iaasStoragePoolId ? $iaasStoragePoolId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'tags'  =>  $model->tags,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            'ssh_username'  =>  $model->ssh_username,
            'ssh_password'  =>  $model->ssh_password,
            'ssh_port'  =>  $model->ssh_port,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
















}
