<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineMigrations;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineMigrationsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'source_iaas_compute_member_id' => 'required|exists:source_iaas_compute_members,uuid|uuid',
        'target_iaas_compute_member_id' => 'required|exists:target_iaas_compute_members,uuid|uuid',
        'source_iaas_storage_volume_id' => 'nullable|exists:source_iaas_storage_volumes,uuid|uuid',
        'target_iaas_storage_volume_id' => 'nullable|exists:target_iaas_storage_volumes,uuid|uuid',
        'source_iaas_storage_member_id' => 'nullable|exists:source_iaas_storage_members,uuid|uuid',
        'target_iaas_storage_member_id' => 'nullable|exists:target_iaas_storage_members,uuid|uuid',
        'status' => '',
        'current_step' => 'nullable',
        'progress' => 'integer',
        'step_message' => 'nullable|string',
        'error_message' => 'nullable|string',
        'options' => 'nullable',
        'started_at' => 'nullable|date',
        'completed_at' => 'nullable|date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}