<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractRepositoriesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for Repositories
 *
 * Class RepositoriesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class RepositoriesService extends AbstractRepositoriesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getIsoRepoForVirtualMachine(VirtualMachines $vm)
    {
        $repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $vm->iaas_cloud_node_id)
            ->where('is_backup_repository', false)
            ->first();

        return $repository;
    }

    public static function getDefaultRepositoryOfVirtualMachine(VirtualMachines $vm)
    {
        return Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $vm->iaas_cloud_node_id)
            ->where('is_backup_repository', false)
            ->first();
    }

    public static function deleteRepoImage(RepositoryImages $image)
    {
        $repoServer = RepositoryImagesService::getRepositoryOfImage($image);

        $command = 'rm ' . $image->path;
        $result = $repoServer->performSSHCommand($command);

        //  Here we will do the check.
    }
}
