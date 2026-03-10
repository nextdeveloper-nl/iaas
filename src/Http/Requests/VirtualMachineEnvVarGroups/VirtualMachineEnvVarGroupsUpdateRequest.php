<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineEnvVarGroups;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineEnvVarGroupsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'iaas_env_var_group_id' => 'nullable|exists:iaas_env_var_groups,uuid|uuid',
        'priority' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}