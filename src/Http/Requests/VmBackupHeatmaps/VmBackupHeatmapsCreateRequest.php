<?php

namespace NextDeveloper\IAAS\Http\Requests\VmBackupHeatmaps;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VmBackupHeatmapsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_backup_job_id' => 'nullable|exists:iaas_backup_jobs,uuid|uuid',
        'job_name' => 'nullable|string',
        'job_type' => 'nullable|string',
        'is_enabled' => 'nullable|boolean',
        'expected_rpo_hours' => 'nullable|numeric',
        'virtual_machine_name' => 'nullable|string',
        'hostname' => 'nullable|string',
        'backup_date' => 'nullable|date',
        'day_offset' => 'nullable|integer',
        'day_of_week' => 'nullable|string',
        'day_status' => 'nullable|string',
        'is_rpo_breach' => 'nullable|boolean',
        'total_runs' => 'nullable|integer',
        'success_runs' => 'nullable|integer',
        'failed_runs' => 'nullable|integer',
        'day_size_bytes' => 'nullable|integer',
        'avg_duration_secs' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}