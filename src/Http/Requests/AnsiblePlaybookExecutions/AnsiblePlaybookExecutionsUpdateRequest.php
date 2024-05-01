<?php

namespace NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookExecutions;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AnsiblePlaybookExecutionsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'is_external_machine' => 'boolean',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'ssh_username' => 'nullable|string',
        'ssh_password' => 'nullable|string',
        'ssh_port' => 'integer',
        'ip_v4' => 'nullable',
        'ip_v6' => 'nullable',
        'last_execution_time' => 'date',
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