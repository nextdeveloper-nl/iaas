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
            'name' => 'nullable',
        'gb_per_hour_price' => 'nullable',
        'is_active' => 'boolean',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'tags' => '',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}