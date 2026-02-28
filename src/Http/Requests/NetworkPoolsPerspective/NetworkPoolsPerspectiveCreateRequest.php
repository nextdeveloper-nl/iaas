<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkPoolsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkPoolsPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'resource_validator' => 'nullable|string',
        'is_active' => 'nullable|boolean',
        'vlan_start' => 'nullable|integer',
        'vlan_end' => 'nullable|integer',
        'vxlan_start' => 'nullable|integer',
        'vxlan_end' => 'nullable|integer',
        'provisioning_alg' => 'nullable|string',
        'price_pergb' => 'nullable',
        'currency' => 'nullable|string',
        'total_networks' => 'nullable|integer',
        'datacenter' => 'nullable|string',
        'cloud_node' => 'nullable|string',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'tags' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}