<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualNetworkCardsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualNetworkCardsPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'mac_addr' => 'nullable',
        'bandwidth_limit' => 'nullable|integer',
        'iaas_network_id' => 'nullable|exists:iaas_networks,uuid|uuid',
        'network' => 'nullable|string',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'virtual_machine' => 'nullable|string',
        'ip_addr' => 'nullable',
        'device_number' => 'nullable|integer',
        'is_draft' => 'nullable|boolean',
        'status' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}