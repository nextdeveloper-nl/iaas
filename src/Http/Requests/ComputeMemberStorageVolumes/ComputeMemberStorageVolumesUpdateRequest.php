<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberStorageVolumes;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberStorageVolumesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'hypervisor_data' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}