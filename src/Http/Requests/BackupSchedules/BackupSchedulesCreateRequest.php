<?php

namespace NextDeveloper\IAAS\Http\Requests\BackupSchedules;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class BackupSchedulesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'required|string',
        'object_id' => 'required',
        'day_of_month' => 'nullable|integer',
        'day_of_week' => 'nullable|integer',
        'time_of_day' => 'required|date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}