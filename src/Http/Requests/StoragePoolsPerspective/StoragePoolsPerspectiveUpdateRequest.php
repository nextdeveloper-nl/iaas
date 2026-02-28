<?php

namespace NextDeveloper\IAAS\Http\Requests\StoragePoolsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StoragePoolsPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'price_pergb' => 'nullable',
        'is_active' => 'nullable|boolean',
        'currency' => 'nullable|string',
        'datacenter' => 'nullable|string',
        'cloud_node' => 'nullable|string',
        'tags' => 'nullable',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'total_hdd' => 'nullable',
        'used_hdd' => 'nullable',
        'free_hdd' => 'nullable',
        'virtual_allocation' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}