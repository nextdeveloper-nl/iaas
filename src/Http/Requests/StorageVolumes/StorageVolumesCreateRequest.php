<?php

namespace NextDeveloper\IAAS\Http\Requests\StorageVolumes;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StorageVolumesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'disk_physical_type' => 'nullable|string',
        'connection_parameters' => 'nullable',
        'total_hdd' => 'integer',
        'used_hdd' => 'integer',
        'free_hdd' => 'nullable|integer',
        'virtual_allocation' => 'integer',
        'is_storage' => 'boolean',
        'is_repo' => 'boolean',
        'is_cdrom' => 'boolean',
        'hypervisor_data' => 'nullable',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'iaas_storage_member_id' => 'nullable|exists:iaas_storage_members,uuid|uuid',
        'is_alive' => 'boolean',
        'tags' => '',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}