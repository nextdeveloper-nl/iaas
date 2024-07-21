<?php

namespace NextDeveloper\IAAS\Http\Requests\Networks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworksUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'is_public' => 'boolean',
        'speed_limit' => 'integer',
        'ip_addr' => 'nullable',
        'ip_addr_range_start' => 'nullable',
        'ip_addr_range_end' => 'nullable',
        'dns_nameservers' => 'nullable',
        'common_domain_id' => 'nullable|exists:common_domains,uuid|uuid',
        'iaas_dhcp_server_id' => 'nullable|exists:iaas_dhcp_servers,uuid|uuid',
        'iaas_gateway_id' => 'nullable|exists:iaas_gateways,uuid|uuid',
        'iaas_network_pool_id' => 'nullable|exists:iaas_network_pools,uuid|uuid',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}