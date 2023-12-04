<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachinesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name'                   => 'nullable|string|max:150',
        'username'               => 'nullable|string|max:20',
        'password'               => 'nullable|string|max:1024',
        'hostname'               => 'nullable|string|max:150',
        'description'            => 'nullable|string',
        'notes'                  => 'nullable|string',
        'os'                     => 'nullable|string|max:30',
        'distro'                 => 'nullable|string|max:30',
        'version'                => 'nullable|string|max:20',
        'domain_type'            => 'nullable',
        'status'                 => '',
        'cpu'                    => 'boolean',
        'ram'                    => 'integer',
        'winrm_enabled'          => 'boolean',
        'available_operations'   => 'nullable',
        'current_operations'     => 'nullable',
        'blocked_operations'     => 'nullable',
        'console_data'           => 'nullable',
        'is_snapshot'            => 'boolean',
        'is_lost'                => 'boolean',
        'is_locked'              => 'boolean',
        'last_metadata_request'  => 'nullable|date',
        'features'               => 'nullable|string',
        'hypervisor_data'        => 'nullable|string',
        'iaas_cloud_node_id'     => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        'from_template_id'       => 'nullable|exists:from_templates,uuid|uuid',
        'suspended_at'           => 'nullable|date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    \n\n\n\n\n\n\n\n\n\n
}