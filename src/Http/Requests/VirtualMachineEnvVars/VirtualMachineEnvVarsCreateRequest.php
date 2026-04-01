<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineEnvVars;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineEnvVarsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'key' => 'required|string',
        'value' => 'nullable|string',
        'source_type' => 'string',
        'source_id' => 'nullable|exists:common_ai.ids,uuid|uuid',
        'is_secret' => 'boolean',
        'description' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}