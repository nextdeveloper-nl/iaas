<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputePoolsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputePoolsPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'virtualization' => 'nullable|string',
        'resource_validator' => 'nullable|string',
        'is_active' => 'nullable|boolean',
        'price_pergb' => 'nullable',
        'currency' => 'nullable|string',
        'total_ram_in_pool' => 'nullable|integer',
        'total_cpu_in_pool' => 'nullable|integer',
        'used_ram_in_pool' => 'nullable|integer',
        'used_cpu_in_pool' => 'nullable|integer',
        'total_vm_in_pool' => 'nullable|integer',
        'running_ram_in_pool' => 'nullable|integer',
        'halted_ram_in_pool' => 'nullable|integer',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'tags' => 'nullable',
        'pool_type' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}