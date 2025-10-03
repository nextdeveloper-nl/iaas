<?php

namespace NextDeveloper\IAAS\Http\Requests\BackupSchedules;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class BackupSchedulesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'day_of_month' => 'nullable|integer',
        'day_of_week' => 'nullable|integer',
        'time_of_day' => 'nullable|date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}