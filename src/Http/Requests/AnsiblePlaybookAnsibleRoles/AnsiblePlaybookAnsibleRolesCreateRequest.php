<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookAnsibleRoles;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsiblePlaybookAnsibleRolesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'position' => 'integer',
        'config' => 'required',
        'iaas_ansible_server_id' => 'required|exists:iaas_ansible_servers,uuid|uuid',
        'iaas_ansible_playbook_id' => 'required|exists:iaas_ansible_playbooks,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}