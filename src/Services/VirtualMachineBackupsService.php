<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachineBackupsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for VirtualMachineBackups
 *
 * Class VirtualMachineBackupsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachineBackupsService extends AbstractVirtualMachineBackupsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getRepositoryImage(VirtualMachineBackups $backup) : ?RepositoryImages
    {
        return RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $backup->iaas_repository_image_id)
            ->first();
    }
}
