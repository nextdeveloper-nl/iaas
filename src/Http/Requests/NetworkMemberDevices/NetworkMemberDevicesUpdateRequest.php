<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkMemberDevices;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkMemberDevicesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'device_identification' => 'nullable|string',
        'is_healthy' => 'boolean',
        'health_information' => 'nullable',
        'device_type' => 'nullable|string',
        'iaas_network_member_id' => 'nullable|exists:iaas_network_members,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}