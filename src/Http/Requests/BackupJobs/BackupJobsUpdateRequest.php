<?php

namespace NextDeveloper\IAAS\Http\Requests\BackupJobs;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class BackupJobsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'type' => 'string',
        'iaas_repository_id' => 'nullable|exists:iaas_repositories,uuid|uuid',
        'iaas_backup_retention_policy_id' => 'nullable|exists:iaas_backup_retention_policies,uuid|uuid',
        'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'email_notification_recipients' => 'nullable',
        'expected_rpo_hours' => 'nullable|numeric',
        'expected_rto_hours' => 'nullable|numeric',
        'is_enabled' => 'nullable|boolean',
        'sla_target_pct' => 'numeric',
        'notification_webhook' => 'nullable|string',
        'max_allowed_failures' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}