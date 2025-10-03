<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuHourlyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineCpuHourlyStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'hour_bucket' => 'nullable|date',
        'avg_cpu' => 'nullable',
        'max_cpu' => 'nullable',
        'min_cpu' => 'nullable',
        'stddev_cpu' => 'nullable',
        'data_points' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}