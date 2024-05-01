<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookAnsibleRoles;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsiblePlaybookAnsibleRolesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'position' => 'integer',
        'config' => 'nullable',
        'iaas_ansible_server_id' => 'nullable|exists:iaas_ansible_servers,uuid|uuid',
        'iaas_ansible_playbook_id' => 'nullable|exists:iaas_ansible_playbooks,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}