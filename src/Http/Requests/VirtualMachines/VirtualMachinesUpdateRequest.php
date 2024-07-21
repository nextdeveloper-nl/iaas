<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachinesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'password' => 'nullable|string',
        'description' => 'nullable|string',
        'ram' => 'nullable|integer',
        'tags' => '',
        'common_domain_id' => 'nullable|exists:common_domains,uuid|uuid',
        'iaas_repository_image_id' => 'nullable|exists:iaas_repository_images,uuid|uuid',
        'iaas_compute_pool_id' => 'exists:iaas_compute_pools,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}