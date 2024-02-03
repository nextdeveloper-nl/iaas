<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMembers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMembersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'hostname' => 'required|string',
        'ip_addr' => 'nullable|string',
        'local_ip_addr' => 'nullable|string',
        'management_data' => 'nullable',
        'features' => 'nullable',
        'is_behind_firewall' => 'boolean',
        'hypervisor_data' => 'nullable|string',
        'total_socket' => 'integer',
        'total_cpu' => 'integer',
        'total_ram' => 'integer',
        'used_cpu' => 'integer',
        'used_ram' => 'integer',
        'total_vm' => 'integer',
        'max_overbooking_ratio' => 'integer',
        'cpu_info' => 'nullable',
        'up_since' => 'required|date',
        'benchmark_score' => 'integer',
        'is_maintenance' => 'boolean',
        'is_alive' => 'boolean',
        'iaas_compute_pool_id' => 'nullable|exists:iaas_compute_pools,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}