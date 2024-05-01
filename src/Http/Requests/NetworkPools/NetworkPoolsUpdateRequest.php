<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkPools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkPoolsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable',
        'vlan_start' => 'integer',
        'vlan_end' => 'integer',
        'vxlan_start' => 'integer',
        'vxlan_end' => 'integer',
        'is_vlan_available' => 'boolean',
        'is_vxlan_available' => 'boolean',
        'is_active' => 'boolean',
        'iaas_datacenter_id' => 'nullable|exists:iaas_datacenters,uuid|uuid',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'provisioning_alg' => 'nullable|string',
        'resource_validator' => 'nullable|string',
        'tags' => '',
        'price_pergb' => '',
        'common_currency_id' => 'nullable|exists:common_currencies,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}