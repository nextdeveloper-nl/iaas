<?php

namespace NextDeveloper\IAAS\Http\Requests\Repositories;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class RepositoriesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ip_addr' => 'nullable',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_vm_repo' => 'boolean',
        'is_iso_repo' => 'boolean',
        'is_docker_registry' => 'boolean',
        'iso_path' => 'nullable|string',
        'vm_path' => 'nullable|string',
        'docker_registry_port' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}