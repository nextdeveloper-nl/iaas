<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybookExecutions;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsibleSystemPlaybookExecutionsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_ansible_system_plays_id' => 'nullable|exists:iaas_ansible_system_plays,uuid|uuid',
        'last_execution_time' => 'date',
        'package' => 'nullable|string',
        'config' => 'nullable',
        'execution_total_time' => 'integer',
        'last_output' => 'nullable|string',
        'result_ok' => 'integer',
        'result_unreachable' => 'integer',
        'result_failed' => 'integer',
        'result_skipped' => 'integer',
        'result_rescued' => 'integer',
        'result_ignored' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}