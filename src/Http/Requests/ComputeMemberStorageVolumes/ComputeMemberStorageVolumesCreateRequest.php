<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberStorageVolumes;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberStorageVolumesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'hypervisor_data' => 'nullable',
        'name' => 'nullable|string',
        'description' => 'nullable|string',
        'block_device_data' => 'nullable',
        'iaas_storage_volume_id' => 'nullable|exists:iaas_storage_volumes,uuid|uuid',
        'iaas_storage_member_id' => 'nullable|exists:iaas_storage_members,uuid|uuid',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}