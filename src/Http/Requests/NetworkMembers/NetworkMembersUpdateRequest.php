<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkMembers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkMembersUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'ip_addr' => 'nullable',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'iaas_network_pool_id' => 'nullable|exists:iaas_network_pools,uuid|uuid',
        'tags' => '',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}