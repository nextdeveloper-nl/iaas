<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualDiskImageStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualDiskImageStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_disk_image_id' => 'required|exists:iaas_virtual_disk_images,uuid|uuid',
        'size' => 'required|integer',
        'physical_utilisation' => 'required|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
