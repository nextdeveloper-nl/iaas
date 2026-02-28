<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMembersPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMembersPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'hostname' => 'nullable|string',
        'ip_addr' => 'nullable',
        'has_warning' => 'nullable|boolean',
        'has_error' => 'nullable|boolean',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ssh_port' => 'nullable|integer',
        'total_socket' => 'nullable|integer',
        'total_cpu' => 'nullable|integer',
        'total_ram' => 'nullable|integer',
        'used_cpu' => 'nullable|integer',
        'used_ram' => 'nullable|integer',
        'free_cpu' => 'nullable|integer',
        'running_vm' => 'nullable|integer',
        'halted_vm' => 'nullable|integer',
        'total_vm' => 'nullable|integer',
        'uptime' => 'nullable|date',
        'idle_time' => 'nullable|date',
        'benchmark_score' => 'nullable|integer',
        'is_in_maintenance' => 'nullable|boolean',
        'is_alive' => 'nullable|boolean',
        'compute_pool_name' => 'nullable|string',
        'iaas_compute_pool_id' => 'nullable|exists:iaas_compute_pools,uuid|uuid',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'states' => 'nullable',
        'tags' => 'nullable',
        'is_event_service_running' => 'nullable|boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}