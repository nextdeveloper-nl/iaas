<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMembers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMembersUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name'                  => 'nullable|string|max:100',
        'hostname'              => 'nullable|string|max:45',
        'ip_addr'               => 'nullable|string|max:15',
        'local_ip_addr'         => 'nullable|string|max:15',
        'api_url'               => 'nullable|string|max:2083',
        'port'                  => 'nullable|integer',
        'username'              => 'nullable|string|max:20',
        'password'              => 'nullable|string|max:1024',
        'features'              => 'nullable|string',
        'is_behind_firewall'    => 'boolean',
        'hypervisor_data'       => 'nullable|string',
        'total_cpu'             => 'integer',
        'total_ram'             => 'integer',
        'used_cpu'              => 'integer',
        'used_ram'              => 'integer',
        'free_cpu'              => 'nullable|integer',
        'free_ram'              => 'nullable|integer',
        'total_vm'              => 'integer',
        'overbooking_ratio'     => 'nullable|numeric',
        'max_overbooking_ratio' => 'integer',
        'cpu_info'              => 'nullable',
        'uptime'                => 'integer',
        'idle_time'             => 'integer',
        'benchmark_score'       => 'integer',
        'is_maintenance'        => 'boolean',
        'is_alive'              => 'boolean',
        'iaas_compute_pool_id'  => 'nullable|exists:iaas_compute_pools,uuid|uuid',
        'iam_account_id'        => 'nullable|exists:iam_accounts,uuid|uuid',
        'iam_user_id'           => 'nullable|exists:iam_users,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}