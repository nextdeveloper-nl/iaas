<?php

namespace NextDeveloper\IAAS\Http\Requests\StorageMembersPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StorageMembersPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'hostname' => 'nullable|string',
        'ip_addr' => 'nullable|string',
        'local_ip_addr' => 'nullable|string',
        'is_healthy' => 'nullable|boolean',
        'has_warning' => 'nullable|boolean',
        'has_error' => 'nullable|boolean',
        'total_disk' => 'nullable|integer',
        'used_disk' => 'nullable|integer',
        'uptime' => 'nullable|date',
        'is_maintenance' => 'nullable|boolean',
        'is_alive' => 'nullable|boolean',
        'storage_pool' => 'nullable|string',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}