<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsiblePlaybooks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsiblePlaybooksCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'description' => 'nullable|string',
        'is_public' => 'boolean',
        'is_procedure' => 'boolean',
        'iaas_ansible_server_id' => 'required|exists:iaas_ansible_servers,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}