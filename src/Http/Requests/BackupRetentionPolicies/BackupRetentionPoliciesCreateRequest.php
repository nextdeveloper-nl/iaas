<?php

namespace NextDeveloper\IAAS\Http\Requests\BackupRetentionPolicies;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class BackupRetentionPoliciesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'description' => 'nullable|string',
        'keep_for_days' => 'integer',
        'keep_last_n_backups' => 'integer',
        'is_public' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}