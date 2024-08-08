<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineBackups;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineBackupsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'backup_window_start' => 'nullable|date',
        'backup_window_end' => 'nullable|date',
        'backup_type' => 'string',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}