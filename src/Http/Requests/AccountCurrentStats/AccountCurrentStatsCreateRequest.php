<?php

namespace NextDeveloper\IAAS\Http\Requests\AccountCurrentStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AccountCurrentStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'vm_count' => 'nullable|integer',
        'total_vcpus' => 'nullable|integer',
        'total_ram_gb' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}