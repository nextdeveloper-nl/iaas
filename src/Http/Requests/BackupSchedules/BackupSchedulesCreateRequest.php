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
            'day_of_month' => 'nullable|integer',
        'day_of_week' => 'nullable|integer',
        'time_of_day' => 'required|date',
        'iaas_backup_job_id' => 'required|exists:iaas_backup_jobs,uuid|uuid',
        'schedule_type' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}