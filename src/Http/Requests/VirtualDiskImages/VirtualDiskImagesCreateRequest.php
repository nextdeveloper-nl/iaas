<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualDiskImages;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualDiskImagesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'size' => 'integer',
        'physical_utilization' => 'integer',
        'available_operations' => 'nullable',
        'current_operations' => 'nullable',
        'is_cdrom' => 'boolean',
        'hypervisor_data' => 'nullable',
        'iaas_storage_volume_id' => 'required|exists:iaas_storage_volumes,uuid|uuid',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'device_number' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}