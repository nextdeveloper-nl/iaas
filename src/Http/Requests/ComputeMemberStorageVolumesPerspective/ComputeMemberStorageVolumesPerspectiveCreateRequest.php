<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberStorageVolumesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberStorageVolumesPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'volume_name' => 'nullable|string',
        'iaas_storage_volume_id' => 'nullable|exists:iaas_storage_volumes,uuid|uuid',
        'storage_pool_name' => 'nullable|string',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'storage_member_name' => 'nullable|string',
        'iaas_storage_member_id' => 'nullable|exists:iaas_storage_members,uuid|uuid',
        'compute_member_name' => 'nullable|string',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'used_hdd' => 'nullable|integer',
        'free_hdd' => 'nullable|integer',
        'disk_physical_type' => 'nullable|string',
        'is_storage' => 'nullable|boolean',
        'is_alive' => 'nullable|boolean',
        'is_cdrom' => 'nullable|boolean',
        'total_hdd' => 'nullable|integer',
        'virtual_allocation' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}