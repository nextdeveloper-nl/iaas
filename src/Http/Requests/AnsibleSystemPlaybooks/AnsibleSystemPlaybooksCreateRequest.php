<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybooks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleSystemPlaybooksCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => 'required|string',
        'name' => 'required|string',
        'description' => 'nullable|string',
        'package' => 'required|string',
        'path' => 'required|string',
        'playbook_filename' => 'required|string',
        'is_public' => 'boolean',
        'is_procedure' => 'boolean',
        'iaas_ansible_server_id' => 'required|exists:iaas_ansible_servers,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}