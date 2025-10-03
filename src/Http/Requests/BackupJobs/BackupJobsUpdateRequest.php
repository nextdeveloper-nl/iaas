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
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}