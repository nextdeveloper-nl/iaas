<?php

namespace NextDeveloper\IAAS\Http\Requests\AccountHourlyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AccountHourlyStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'stat_hour' => 'nullable|date',
        'vm_count' => 'nullable|integer',
        'total_vcpus' => 'nullable|integer',
        'total_ram_gb' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}