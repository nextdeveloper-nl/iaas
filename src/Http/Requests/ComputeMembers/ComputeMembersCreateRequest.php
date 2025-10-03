<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMembers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMembersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'hostname' => 'nullable|string',
        'ip_addr' => 'nullable',
        'local_ip_addr' => 'nullable',
        'is_behind_firewall' => 'boolean',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ssh_port' => 'integer',
        'max_overbooking_ratio' => 'integer',
        'is_in_maintenance' => 'boolean',
        'is_alive' => 'boolean',
        'iaas_compute_pool_id' => 'nullable|exists:iaas_compute_pools,uuid|uuid',
        'tags' => '',
        'events_token' => 'nullable|string',
        'is_event_service_running' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}