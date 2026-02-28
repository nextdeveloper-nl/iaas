<?php

namespace NextDeveloper\IAAS\Http\Requests\RepositoriesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class RepositoriesPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'ip_addr' => 'nullable',
        'local_ip_addr' => 'nullable',
        'is_active' => 'nullable|boolean',
        'is_vm_repo' => 'nullable|boolean',
        'is_iso_repo' => 'nullable|boolean',
        'is_docker_registry' => 'nullable|boolean',
        'repository_maintainer' => 'nullable|string',
        'iso_image_count' => 'nullable|integer',
        'vm_image_count' => 'nullable|integer',
        'states' => 'nullable',
        'is_backup_repository' => 'nullable|boolean',
        'price_pergb' => 'nullable',
        'common_currency_id' => 'nullable|exists:common_currencies,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}