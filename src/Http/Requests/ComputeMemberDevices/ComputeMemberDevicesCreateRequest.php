<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberDevices;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberDevicesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'device_identification' => 'required|string',
        'is_healthy' => 'boolean',
        'health_information' => 'nullable',
        'device_type' => 'nullable|string',
        'iaas_compute_member_id' => 'required|exists:iaas_compute_members,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}