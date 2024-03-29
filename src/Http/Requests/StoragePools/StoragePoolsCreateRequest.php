<?php

namespace NextDeveloper\IAAS\Http\Requests\StoragePools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StoragePoolsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
        'gb_per_hour_price' => 'required',
        'is_active' => 'boolean',
        'iaas_cloud_node_id' => 'required|exists:iaas_cloud_nodes,uuid|uuid',
        'tags' => '',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}