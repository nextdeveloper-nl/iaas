<?php

namespace NextDeveloper\IAAS\Http\Requests\BackupJobReplications;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class BackupJobReplicationsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_backup_job_id' => 'required|exists:iaas_backup_jobs,uuid|uuid',
        'iaas_repository_id' => 'required|exists:iaas_repositories,uuid|uuid',
        'replication_type' => 'string',
        'iaas_backup_retention_policy_id' => 'nullable|exists:iaas_backup_retention_policies,uuid|uuid',
        'priority' => 'integer',
        'is_enabled' => 'boolean',
        'encrypt_in_transit' => 'boolean',
        'bandwidth_limit_mbps' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}