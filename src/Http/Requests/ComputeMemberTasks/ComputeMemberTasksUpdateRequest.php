<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberTasks;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberTasksUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'error' => 'nullable|string',
        'progress' => 'nullable|integer',
        'hypervisor_data' => 'nullable',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}