<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberNetworkInterfaces;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberNetworkInterfacesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'device' => 'nullable|string',
        'mac_addr' => 'required',
        'vlan' => 'nullable|integer',
        'mtu' => 'nullable|integer',
        'is_management' => 'boolean',
        'is_default' => 'boolean',
        'is_connected' => 'boolean',
        'hypervisor_data' => 'nullable',
        'iaas_compute_member_id' => 'required|exists:iaas_compute_members,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}