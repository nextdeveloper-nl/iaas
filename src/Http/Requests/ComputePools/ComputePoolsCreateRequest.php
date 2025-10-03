<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputePools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputePoolsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'resource_validator' => 'nullable|string',
        'pool_data' => 'nullable',
        'virtualization' => 'string',
        'provisioning_alg' => 'nullable|string',
        'is_active' => 'boolean',
        'is_alive' => 'boolean',
        'is_public' => 'boolean',
        'iaas_datacenter_id' => 'nullable|exists:iaas_datacenters,uuid|uuid',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'tags' => '',
        'price_pergb' => '',
        'common_currency_id' => 'nullable|exists:common_currencies,uuid|uuid',
        'pool_type' => 'string',
        'price_pergb_month' => 'nullable',
        'disk_ram_ratio' => '',
        'code_name' => 'string',
        'is_default' => 'boolean',
        'is_iso27001_enabled' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}