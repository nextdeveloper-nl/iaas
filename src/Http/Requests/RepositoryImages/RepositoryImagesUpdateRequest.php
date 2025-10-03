<?php

namespace NextDeveloper\IAAS\Http\Requests\RepositoryImages;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class RepositoryImagesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'path' => 'nullable|string',
        'filename' => 'nullable|string',
        'default_username' => 'nullable|string',
        'default_password' => 'nullable|string',
        'is_active' => 'boolean',
        'is_iso' => 'boolean',
        'is_virtual_machine_image' => 'boolean',
        'is_docker_image' => 'boolean',
        'os' => 'nullable|string',
        'distro' => 'nullable|string',
        'version' => 'nullable|string',
        'release_version' => 'nullable|string',
        'is_latest' => 'boolean',
        'extra' => 'nullable|string',
        'cpu_type' => 'nullable|string',
        'supported_virtualizations' => 'nullable',
        'iaas_repository_id' => 'nullable|exists:iaas_repositories,uuid|uuid',
        'hash' => 'nullable|string',
        'size' => 'nullable|integer',
        'ram' => 'integer',
        'cpu' => 'integer',
        'is_public' => 'boolean',
        'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'has_plusclouds_service' => 'boolean',
        'is_cloudinit_image' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}