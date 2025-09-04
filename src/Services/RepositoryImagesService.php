<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Actions\Repositories\SynchronizeIsos;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractRepositoryImagesService;
use NextDeveloper\IAAS\Services\Repositories\SyncRepositoryService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for RepositoryImages
 *
 * Class RepositoryImagesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class RepositoryImagesService extends AbstractRepositoryImagesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
    public static function getRepositoryOfImage(RepositoryImages $image) : Repositories
    {
        return Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $image->iaas_repository_id)
            ->first();
    }

    public static function updateRepoSize(RepositoryImages $image) : RepositoryImages
    {
        SyncRepositoryService::syncRepoImage($image);

        return $image->fresh();
    }

    public static function getCloudInitImage(VirtualMachines $vm)
    {
        return RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_repository_id', $vm->iaas_cloud_node->iaas_cloud_node_repository_id)
            ->where('filename', 'config-' . $vm->uuid . '.iso')
            ->where('type', 'iso')
            ->where('is_active', true)
            ->first();
    }

    public static function syncRepoImageByFilename($filename, Repositories $repo, $type = 'iso', $isActive = true)
    {
        (new SynchronizeIsos($repo))->handle();

        return RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_repository_id', $repo->id)
            ->where('filename', $filename)
            ->where('type', $type)
            ->first();
    }
}
