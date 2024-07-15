<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybooks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleSystemPlaybooksUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => 'nullable|string',
        'name' => 'nullable|string',
        'description' => 'nullable|string',
        'package' => 'nullable|string',
        'path' => 'nullable|string',
        'playbook_filename' => 'nullable|string',
        'is_public' => 'boolean',
        'is_procedure' => 'boolean',
        'iaas_ansible_server_id' => 'nullable|exists:iaas_ansible_servers,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}