<?php

namespace NextDeveloper\IAAS\Http\Requests\HealthChecks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class HealthChecksUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'check_type' => 'string',
        'check_status' => 'nullable|string',
        'severity' => 'string',
        'checked_at' => 'date',
        'next_check_at' => 'nullable|date',
        'response_time_ms' => 'nullable|integer',
        'error_message' => 'nullable|string',
        'error_code' => 'nullable|string',
        'check_data' => 'nullable',
        'metadata' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}