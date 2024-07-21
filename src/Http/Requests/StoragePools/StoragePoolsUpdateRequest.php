<?php

namespace NextDeveloper\IAAS\Http\Requests\StoragePools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StoragePoolsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'price_pergb' => '',
        'is_active' => 'boolean',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'tags' => '',
        'common_currency_id' => 'nullable|exists:common_currencies,uuid|uuid',
        'iaas_datacenter_id' => 'nullable|exists:iaas_datacenters,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}