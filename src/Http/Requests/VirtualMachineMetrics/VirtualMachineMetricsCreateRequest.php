<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineMetrics;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineMetricsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'parameter' => 'nullable|string',
        'value' => 'numeric',
        'timestamp' => 'date',
        'source' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}