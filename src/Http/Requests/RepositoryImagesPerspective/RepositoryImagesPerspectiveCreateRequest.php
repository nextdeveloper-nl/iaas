<?php

namespace NextDeveloper\IAAS\Http\Requests\RepositoryImagesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class RepositoryImagesPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'image_name' => 'nullable|string',
        'description' => 'nullable|string',
        'post_boot_script' => 'nullable|string',
        'os' => 'nullable|string',
        'distro' => 'nullable|string',
        'version' => 'nullable|string',
        'cpu_type' => 'nullable|string',
        'extra' => 'nullable|string',
        'release_version' => 'nullable|string',
        'is_latest' => 'nullable|boolean',
        'supported_virtualizations' => 'nullable',
        'is_iso' => 'nullable|boolean',
        'is_public' => 'nullable|boolean',
        'is_virtual_machine_image' => 'nullable|boolean',
        'is_docker_image' => 'nullable|boolean',
        'cpu' => 'nullable|integer',
        'ram' => 'nullable|integer',
        'size' => 'nullable|integer',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'iaas_repository_id' => 'nullable|exists:iaas_repositories,uuid|uuid',
        'repository_name' => 'nullable|string',
        'is_backup_repository' => 'nullable|boolean',
        'has_plusclouds_service' => 'nullable|boolean',
        'is_backup' => 'nullable|boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}