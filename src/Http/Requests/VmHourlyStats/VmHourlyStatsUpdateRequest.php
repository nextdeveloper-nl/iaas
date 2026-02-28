<?php

namespace NextDeveloper\IAAS\Http\Requests\VmHourlyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VmHourlyStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'stat_hour' => 'nullable|date',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'cpu' => 'nullable|integer',
        'ram' => 'nullable|integer',
        'valid_from' => 'nullable|date',
        'valid_to' => 'nullable|date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}