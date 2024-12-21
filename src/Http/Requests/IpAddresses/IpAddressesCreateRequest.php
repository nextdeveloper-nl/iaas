<?php

namespace NextDeveloper\IAAS\Http\Requests\IpAddresses;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class IpAddressesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'ip_addr' => 'required',
        'is_reserved' => 'boolean',
        'iaas_network_id' => 'required|exists:iaas_networks,uuid|uuid',
        'iaas_virtual_network_card_id' => 'nullable|exists:iaas_virtual_network_cards,uuid|uuid',
        'custom_mac_addr' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}