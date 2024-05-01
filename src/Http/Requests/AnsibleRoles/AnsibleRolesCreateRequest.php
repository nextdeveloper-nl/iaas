<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleRoles;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleRolesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'version' => 'nullable|integer',
        'release_number' => 'integer',
        'config' => 'required',
        'hash' => 'required|string',
        'min_ansible_version' => 'nullable|string',
        'prerequisites' => 'nullable|string',
        'is_active' => 'boolean',
        'is_procedure' => 'boolean',
        'iaas_ansible_server_id' => 'required|exists:iaas_ansible_servers,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}