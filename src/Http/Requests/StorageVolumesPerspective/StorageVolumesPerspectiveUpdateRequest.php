<?php

namespace NextDeveloper\IAAS\Http\Requests\StorageVolumesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StorageVolumesPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'disk_physical_type' => 'nullable|string',
        'total_hdd' => 'nullable|integer',
        'free_hdd' => 'nullable|integer',
        'virtual_allocation' => 'nullable|integer',
        'is_storage' => 'nullable|boolean',
        'is_repo' => 'nullable|boolean',
        'is_cdrom' => 'nullable|boolean',
        'storage_pool' => 'nullable|string',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'storage_member' => 'nullable|string',
        'iaas_storage_member_id' => 'nullable|exists:iaas_storage_members,uuid|uuid',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}