<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachinesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'password' => 'nullable|string',
        'description' => 'nullable|string',
        'ram' => 'required|integer',
        'tags' => '',
        'common_domain_id' => 'nullable|exists:common_domains,uuid|uuid',
        'iaas_repository_image_id' => 'nullable|exists:iaas_repository_images,uuid|uuid',
        'iaas_compute_pool_id' => 'exists:iaas_compute_pools,uuid|uuid',
        'auto_backup_interval' => 'string',
        'auto_backup_time' => 'string',
        'snapshot_of_virtual_machine' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}