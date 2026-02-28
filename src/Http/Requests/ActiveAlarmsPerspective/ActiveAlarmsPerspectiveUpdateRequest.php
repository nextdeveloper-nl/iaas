<?php

namespace NextDeveloper\IAAS\Http\Requests\ActiveAlarmsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ActiveAlarmsPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'check_type' => 'nullable|string',
        'check_status' => 'nullable|string',
        'severity' => 'nullable|string',
        'error_message' => 'nullable|string',
        'error_code' => 'nullable|string',
        'response_time_ms' => 'nullable|integer',
        'checked_at' => 'nullable|date',
        'next_check_at' => 'nullable|date',
        'check_data' => 'nullable',
        'metadata' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}