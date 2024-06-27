<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineMetrics;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineMetricsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'parameter' => 'nullable|string',
        'value' => '',
        'timestamp' => 'date',
        'source' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}