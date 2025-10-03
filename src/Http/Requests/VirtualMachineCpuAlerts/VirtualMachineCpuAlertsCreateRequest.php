<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuAlerts;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineCpuAlertsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'alert_time' => 'date',
        'current_cpu' => 'nullable',
        'sma_9' => 'nullable',
        'deviation' => 'nullable',
        'severity' => 'required|string',
        'alert_reason' => 'nullable|string',
        'check_duration_ms' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}