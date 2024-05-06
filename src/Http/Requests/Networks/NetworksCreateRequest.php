<?php

namespace NextDeveloper\IAAS\Http\Requests\Networks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworksCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'vlan' => 'required|integer',
        'vxlan' => 'nullable|string',
        'bandwidth' => 'nullable|integer',
        'is_public' => 'boolean',
        'is_vpn' => 'boolean',
        'is_management' => 'boolean',
        'ip_addr' => 'nullable',
        'ip_addr_range_start' => 'nullable',
        'ip_addr_range_end' => 'nullable',
        'dns_nameservers' => 'nullable',
        'mtu' => 'integer',
        'common_domain_id' => 'nullable|exists:common_domains,uuid|uuid',
        'iaas_dhcp_server_id' => 'nullable|exists:iaas_dhcp_servers,uuid|uuid',
        'iaas_gateway_id' => 'nullable|exists:iaas_gateways,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}