<?php

namespace NextDeveloper\IAAS\Http\Requests\MonitoringInstances;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class MonitoringInstancesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'configuration' => '',
        'instance_type' => 'string',
        'is_active' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}