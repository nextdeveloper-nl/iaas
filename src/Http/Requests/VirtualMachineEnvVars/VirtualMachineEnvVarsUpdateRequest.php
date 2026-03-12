<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineEnvVars;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineEnvVarsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'key' => 'nullable|string',
        'value' => 'nullable|string',
        'is_secret' => 'boolean',
        'description' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
