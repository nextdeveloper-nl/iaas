<?php

namespace NextDeveloper\IAAS\Http\Requests\Gateways;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class GatewaysCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'gateway_data' => 'nullable',
        'is_public' => 'boolean',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ip_addr' => 'required',
        'api_token' => 'nullable|string',
        'api_url' => 'nullable|string',
        'gateway_type' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}