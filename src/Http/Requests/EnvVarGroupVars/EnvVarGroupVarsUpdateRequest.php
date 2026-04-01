<?php

namespace NextDeveloper\IAAS\Http\Requests\EnvVarGroupVars;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class EnvVarGroupVarsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_env_var_group_id' => 'nullable|exists:iaas_env_var_groups,uuid|uuid',
        'key' => 'nullable|string',
        'value' => 'nullable|string',
        'source_type' => 'string',
        'source_id' => 'nullable|exists:common_ai.ids,uuid|uuid',
        'is_secret' => 'boolean',
        'description' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}