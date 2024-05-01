<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsiblePlaybooks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsiblePlaybooksUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'is_public' => 'boolean',
        'is_procedure' => 'boolean',
        'ansible_server_id' => 'nullable|exists:ansible_servers,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}