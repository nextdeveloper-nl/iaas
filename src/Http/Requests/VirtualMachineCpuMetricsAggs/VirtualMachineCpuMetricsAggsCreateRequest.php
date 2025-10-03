<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuMetricsAggs;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineCpuMetricsAggsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'timestamp' => 'required|date',
        'avg_cpu' => 'required',
        'sma9' => 'nullable',
        'stddev9' => 'nullable',
        'ema9' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}