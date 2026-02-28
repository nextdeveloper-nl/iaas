<?php

namespace NextDeveloper\IAAS\Http\Requests\CloudNodesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class CloudNodesPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'is_public' => 'nullable|boolean',
        'is_alive' => 'nullable|boolean',
        'is_in_maintenance' => 'nullable|boolean',
        'datacenter_name' => 'nullable|string',
        'compute_pool_count' => 'nullable|integer',
        'storage_pool_count' => 'nullable|integer',
        'network_pool_count' => 'nullable|integer',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}