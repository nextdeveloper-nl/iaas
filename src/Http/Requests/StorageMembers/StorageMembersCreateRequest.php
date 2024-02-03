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
        'hostname' => 'required|string',
        'ip_addr' => 'nullable|string',
        'local_ip_addr' => 'nullable|string',
        'management_data' => 'nullable',
        'features' => 'nullable',
        'is_behind_firewall' => 'boolean',
        'total_socket' => 'integer',
        'total_cpu' => 'integer',
        'total_ram' => 'integer',
        'total_disk' => 'integer',
        'used_disk' => 'integer',
        'disk_info' => 'nullable',
        'up_since' => 'required|date',
        'benchmark_score' => 'integer',
        'is_maintenance' => 'boolean',
        'is_alive' => 'boolean',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}