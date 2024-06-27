<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberMetrics;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberMetricsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'source' => 'string',
        'iaas_compute_member_id' => 'required|exists:iaas_compute_members,uuid|uuid',
        'parameter' => 'nullable|string',
        'value' => '',
        'timestamp' => 'date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}