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
            'local_ip_addr' => 'nullable',
            'is_behind_firewall' => 'boolean',
            'is_management_agent_available' => 'boolean',
            'ssh_port' => 'integer',
            'is_backup_repository' => 'boolean',
            'price_pergb' => 'nullable',
            'common_currency_id' => 'nullable|exists:common_currencies,uuid|uuid',
            'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
            'registry_username' => 'string',
            'registry_password' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
