<?php

namespace NextDeveloper\IAAS\Http\Requests\CustomerResourcesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class CustomerResourcesPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'account_name' => 'nullable|string',
        'user_name' => 'nullable|string',
        'user_email' => 'nullable|string',
        'is_account_suspended' => 'nullable|boolean',
        'is_crm_suspended' => 'nullable|boolean',
        'is_crm_disabled' => 'nullable|boolean',
        'is_accounting_disabled' => 'nullable|boolean',
        'resource_type' => 'nullable|string',
        'resource_id' => 'nullable|exists:resources,uuid|uuid',
        'resource_name' => 'nullable|string',
        'resource_status' => 'nullable|string',
        'cpu' => 'nullable|integer',
        'ram' => 'nullable|integer',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'hypervisor_name_label' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}