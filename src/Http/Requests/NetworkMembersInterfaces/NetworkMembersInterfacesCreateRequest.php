<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkMembersInterfaces;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkMembersInterfacesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'ip_addr' => 'required',
        'configuration' => 'nullable|string',
        'iaas_network_member_id' => 'required|exists:iaas_network_members,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}