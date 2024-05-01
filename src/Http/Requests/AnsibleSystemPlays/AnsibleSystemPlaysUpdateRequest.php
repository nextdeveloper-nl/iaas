<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlays;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleSystemPlaysUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'iaas_ansible_system_playbook_id' => 'nullable|exists:iaas_ansible_system_playbooks,uuid|uuid',
        'hosts' => 'nullable|string',
        'roles' => 'nullable',
        'config' => 'nullable',
        'become' => 'boolean',
        'gather_facts' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}