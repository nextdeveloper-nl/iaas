<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkMembersPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkMembersPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'ip_addr' => 'nullable',
        'network_pool_name' => 'nullable|string',
        'iaas_network_pool_id' => 'nullable|exists:iaas_network_pools,uuid|uuid',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'tags' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}