<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineBackupsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineBackupsPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'path' => 'nullable|string',
        'filename' => 'nullable|string',
        'cpu' => 'nullable|integer',
        'ram' => 'nullable|integer',
        'backup_type' => 'nullable|string',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'status' => 'nullable|string',
        'backup_starts' => 'nullable|date',
        'backup_ends' => 'nullable|date',
        'iaas_repository_image_id' => 'nullable|exists:iaas_repository_images,uuid|uuid',
        'iaas_repository_id' => 'nullable|exists:iaas_repositories,uuid|uuid',
        'iaas_backup_job_id' => 'nullable|exists:iaas_backup_jobs,uuid|uuid',
        'progress' => 'nullable|integer',
        'hash' => 'nullable|string',
        'is_latest' => 'nullable|boolean',
        'size' => 'nullable|integer',
        'os' => 'nullable|string',
        'distro' => 'nullable|string',
        'cpu_type' => 'nullable|string',
        'supported_virtualizations' => 'nullable',
        'backup_job_type' => 'nullable|string',
        'retention_policy_name' => 'nullable|string',
        'keep_for_days' => 'nullable|integer',
        'keep_last_n_backups' => 'nullable|integer',
        'hostname' => 'nullable|string',
        'virtual_machine_name' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}