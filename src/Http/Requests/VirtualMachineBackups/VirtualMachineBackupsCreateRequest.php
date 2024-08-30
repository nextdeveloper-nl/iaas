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
        'backup_window_start' => 'required|date',
        'backup_window_end' => 'required|date',
        'backup_type' => 'string',
        'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'status' => 'string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}