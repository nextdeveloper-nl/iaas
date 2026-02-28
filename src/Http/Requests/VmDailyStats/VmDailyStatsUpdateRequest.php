<?php

namespace NextDeveloper\IAAS\Http\Requests\VmDailyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VmDailyStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'stat_date' => 'nullable|date',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'avg_vcpus' => 'nullable',
        'max_vcpus' => 'nullable|integer',
        'avg_ram_gb' => 'nullable',
        'max_ram_gb' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}