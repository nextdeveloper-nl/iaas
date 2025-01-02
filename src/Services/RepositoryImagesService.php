<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
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
}
