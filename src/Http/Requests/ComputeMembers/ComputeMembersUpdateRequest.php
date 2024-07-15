<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMembers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMembersUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'hostname' => 'nullable|string',
        'ip_addr' => 'nullable',
        'local_ip_addr' => 'nullable',
        'management_data' => 'nullable',
        'features' => 'nullable',
        'is_behind_firewall' => 'boolean',
        'max_overbooking_ratio' => 'integer',
        'is_in_maintenance' => 'boolean',
        'is_alive' => 'boolean',
        'iaas_compute_pool_id' => 'nullable|exists:iaas_compute_pools,uuid|uuid',
        'tags' => '',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ssh_port' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}