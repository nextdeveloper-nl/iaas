<?php

namespace NextDeveloper\IAAS\Http\Requests\IpAddresses;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class IpAddressesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'ip_addr' => 'nullable',
        'is_reserved' => 'boolean',
        'iaas_network_id' => 'nullable|exists:iaas_networks,uuid|uuid',
        'iaas_virtual_network_card_id' => 'nullable|exists:iaas_virtual_network_cards,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}