<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualDiskImageStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualDiskImageStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_disk_images_id' => 'nullable|exists:iaas_virtual_disk_images,uuid|uuid',
        'size' => 'nullable|integer',
        'physical_utilisation' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}