<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualNetworkCards;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualNetworkCardsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'mac_addr' => 'nullable',
        'bandwidth_limit' => 'integer',
        'hypervisor_data' => 'nullable',
        'iaas_network_id' => 'nullable|exists:iaas_networks,uuid|uuid',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'device_number' => 'integer',
        'is_draft' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}