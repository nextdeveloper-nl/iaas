<?php

namespace NextDeveloper\IAAS\Http\Requests\DockerContainers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class DockerContainersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
            'image' => 'required|string',
            'command' => 'nullable|array',
            'env_vars' => 'nullable|array',
            'ports' => 'nullable|array',
            'restart_policy' => 'nullable|string|in:no,always,on-failure,unless-stopped',
            'cpu_limit' => 'nullable|numeric|min:0',
            'memory_limit_mb' => 'nullable|integer|min:0',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
