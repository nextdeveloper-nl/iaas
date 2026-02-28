<?php

namespace NextDeveloper\IAAS\Http\Requests\LargestTenantsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class LargestTenantsPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_account_id' => 'nullable|exists:iaas_accounts,uuid|uuid',
        'vm_count' => 'nullable|integer',
        'vcpu_total' => 'nullable|integer',
        'ram_total_gb' => 'nullable|integer',
        'disk_count' => 'nullable|integer',
        'storage_gb' => 'nullable',
        'network_count' => 'nullable|integer',
        'bandwidth_gbps' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}