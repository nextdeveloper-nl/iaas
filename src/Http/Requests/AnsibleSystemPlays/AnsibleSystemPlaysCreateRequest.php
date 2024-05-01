<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlays;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleSystemPlaysCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'iaas_ansible_system_playbook_id' => 'required|exists:iaas_ansible_system_playbooks,uuid|uuid',
        'hosts' => 'nullable|string',
        'roles' => 'required',
        'config' => 'required',
        'become' => 'boolean',
        'gather_facts' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}