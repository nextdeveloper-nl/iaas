<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkPools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkPoolsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name'                    => 'required|string|max:100',
        'vlan_start'              => 'integer',
        'vlan_end'                => 'integer',
        'vxlan_start'             => 'integer',
        'vxlan_end'               => 'integer',
        'has_vlan_support'        => 'boolean',
        'has_vxlan_support'       => 'boolean',
        'is_active'               => 'boolean',
        'iaas_datacenter_id'      => 'nullable|exists:iaas_datacenters,uuid|uuid',
        'iaas_cloud_node_id'      => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'provisioning_alg'        => 'nullable|string|max:500',
        'management_package_name' => 'nullable|string|max:500',
        'resource_validator'      => 'nullable|string|max:500',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}