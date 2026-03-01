<?php

namespace NextDeveloper\IAAS\Http\Requests\VmBackupStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VmBackupStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'vms_protected' => 'nullable|integer',
        'vms_protected_delta' => 'nullable|integer',
        'vms_protected_delta_pct' => 'nullable',
        'rpo_breached_vms' => 'nullable|integer',
        'sla_breached_jobs' => 'nullable|integer',
        'jobs_disabled' => 'nullable|integer',
        'jobs_failed_24h' => 'nullable|integer',
        'jobs_failed_30d' => 'nullable|integer',
        'avg_rpo_achieved_hours' => 'nullable',
        'avg_rpo_target_hours' => 'nullable',
        'storage_used_bytes' => 'nullable|integer',
        'storage_used_gb' => 'nullable',
        'storage_used_tb' => 'nullable',
        'protections_done_24h' => 'nullable|integer',
        'protections_done_30d' => 'nullable|integer',
        'protections_done_delta' => 'nullable|integer',
        'protections_done_delta_pct' => 'nullable',
        'jobs_with_replication' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}