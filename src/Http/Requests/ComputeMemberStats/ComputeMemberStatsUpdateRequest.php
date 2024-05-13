<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        'used_ram' => 'integer',
        'used_cpu' => 'integer',
        'running_vm' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}