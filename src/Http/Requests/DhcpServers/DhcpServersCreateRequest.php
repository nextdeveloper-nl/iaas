<?php

namespace NextDeveloper\IAAS\Http\Requests\DhcpServers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class DhcpServersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'dhcp_data' => 'nullable',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ip_addr' => 'required',
        'api_token' => 'nullable|string',
        'api_url' => 'nullable|string',
        'server_type' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}