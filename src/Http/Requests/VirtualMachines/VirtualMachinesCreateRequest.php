<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachinesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'username' => 'nullable|string',
        'password' => 'nullable|string',
        'hostname' => 'nullable|string',
        'description' => 'nullable|string',
        'os' => 'nullable|string',
        'distro' => 'nullable|string',
        'version' => 'nullable|string',
        'domain_type' => 'nullable|string',
        'status' => 'string',
        'cpu' => 'required|integer',
        'ram' => 'required|integer',
        'winrm_enabled' => 'boolean',
        'available_operations' => 'nullable',
        'current_operations' => 'nullable',
        'blocked_operations' => 'nullable',
        'console_data' => 'nullable',
        'is_snapshot' => 'boolean',
        'is_lost' => 'boolean',
        'is_locked' => 'boolean',
        'last_metadata_request' => 'nullable|date',
        'features' => 'nullable',
        'hypervisor_data' => 'nullable',
        'iaas_cloud_node_id' => 'required|exists:iaas_cloud_nodes,uuid|uuid',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        'iaas_virtual_machines_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}