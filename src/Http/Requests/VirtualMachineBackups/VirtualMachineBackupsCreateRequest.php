<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineBackups;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineBackupsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'description' => 'nullable|string',
        'backup_type' => 'string',
        'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'status' => 'string',
        'backup_starts' => 'date',
        'backup_ends' => 'nullable|date',
        'iaas_repository_image_id' => 'nullable|exists:iaas_repository_images,uuid|uuid',
        'iaas_backup_job_id' => 'nullable|exists:iaas_backup_jobs,uuid|uuid',
        'data' => 'nullable',
        'progress' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}