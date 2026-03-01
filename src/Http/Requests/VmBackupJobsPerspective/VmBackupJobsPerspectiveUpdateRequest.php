<?php

namespace NextDeveloper\IAAS\Http\Requests\VmBackupJobsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VmBackupJobsPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'job_name' => 'nullable|string',
        'job_type' => 'nullable|string',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'is_enabled' => 'nullable|boolean',
        'expected_rpo_hours' => 'nullable|numeric',
        'expected_rto_hours' => 'nullable|numeric',
        'max_allowed_failures' => 'nullable|integer',
        'sla_target_pct' => 'nullable|numeric',
        'notification_webhook' => 'nullable|string',
        'email_notification_recipients' => 'nullable',
        'virtual_machine_name' => 'nullable|string',
        'hostname' => 'nullable|string',
        'retention_policy_name' => 'nullable|string',
        'keep_for_days' => 'nullable|integer',
        'keep_last_n_backups' => 'nullable|integer',
        'is_scheduled' => 'nullable|boolean',
        'last_run_at' => 'nullable|date',
        'last_run_ended_at' => 'nullable|date',
        'last_run_status' => 'nullable|string',
        'last_run_progress' => 'nullable|integer',
        'last_run_duration_secs' => 'nullable|integer',
        'last_run_size_bytes' => 'nullable|integer',
        'consecutive_failures' => 'nullable|integer',
        'rpo_breached' => 'nullable|boolean',
        'rpo_achieved_hours' => 'nullable|integer',
        'sla_breached' => 'nullable|boolean',
        'status_indicator' => 'nullable|string',
        'replication_count' => 'nullable|integer',
        'replication_ok_count' => 'nullable|integer',
        'replication_failed_count' => 'nullable|integer',
        'last_replication_at' => 'nullable|date',
        'replication_status_indicator' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}