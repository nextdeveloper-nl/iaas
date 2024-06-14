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
        'hypervisor_data' => 'nullable',
        'device_number' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}