<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualDiskImages;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualDiskImagesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'size' => 'integer',
        'hypervisor_data' => 'nullable',
        'iaas_storage_volume_id' => 'nullable|exists:iaas_storage_volumes,uuid|uuid',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'device_number' => 'integer',
        'iaas_storage_pool_id' => 'nullable|exists:iaas_storage_pools,uuid|uuid',
        'vbd_hypervisor_data' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}