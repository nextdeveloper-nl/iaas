<?php

namespace NextDeveloper\IAAS\Http\Requests\StorageMembers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StorageMembersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'hostname' => 'nullable|string',
        'ip_addr' => 'nullable|string',
        'local_ip_addr' => 'nullable|string',
        'management_data' => 'nullable',
        'configuration_data' => 'nullable|string',
        'is_healthy' => 'boolean',
        'has_warning' => 'boolean',
        'has_error' => 'boolean',
        'features' => 'nullable',
        'is_behind_firewall' => 'boolean',
        'total_socket' => 'integer',
        'total_cpu' => 'integer',
        'total_ram' => 'integer',
        'total_disk' => 'integer',
        'used_disk' => 'integer',
        'disk_info' => 'nullable',
        'uptime' => 'nullable|date',
        'idle_time' => 'nullable|date',
        'benchmark_score' => 'integer',
        'is_maintenance' => 'boolean',
        'is_alive' => 'boolean',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'tags' => '',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ssh_port' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}