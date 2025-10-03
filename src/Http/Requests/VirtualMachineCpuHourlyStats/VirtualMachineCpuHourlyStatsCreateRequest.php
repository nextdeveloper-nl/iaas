<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuHourlyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineCpuHourlyStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'hour_bucket' => 'required|date',
        'avg_cpu' => 'nullable',
        'max_cpu' => 'nullable',
        'min_cpu' => 'nullable',
        'stddev_cpu' => 'nullable',
        'data_points' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}