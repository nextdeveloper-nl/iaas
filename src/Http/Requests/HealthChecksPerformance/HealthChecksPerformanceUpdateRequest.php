<?php

namespace NextDeveloper\IAAS\Http\Requests\HealthChecksPerformance;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class HealthChecksPerformanceUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'total_checks' => 'nullable|integer',
        'healthy_count' => 'nullable|integer',
        'warning_count' => 'nullable|integer',
        'critical_count' => 'nullable|integer',
        'failed_count' => 'nullable|integer',
        'last_check_at' => 'nullable|date',
        'avg_response_time_ms' => 'nullable',
        'overall_status' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}