<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkMembersInterfaces;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkMembersInterfacesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'ip_addr' => 'nullable',
        'configuration' => 'nullable|string',
        'iaas_network_member_id' => 'nullable|exists:iaas_network_members,uuid|uuid',
        'is_up' => 'boolean',
        'iaas_network_id' => 'nullable|exists:iaas_networks,uuid|uuid',
        'is_shutdown' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}