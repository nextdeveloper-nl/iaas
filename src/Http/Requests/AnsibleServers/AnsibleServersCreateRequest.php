<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleServers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleServersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'is_external_machine' => 'boolean',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ssh_port' => 'integer',
        'ip_v4' => 'nullable',
        'ip_v6' => 'nullable',
        'ansible_version' => 'nullable|integer',
        'roles_path' => 'nullable|string',
        'system_playbooks_path' => 'nullable|string',
        'execution_path' => 'nullable|string',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'price_persecond' => '',
        'common_currency_id' => 'nullable|exists:common_currencies,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}