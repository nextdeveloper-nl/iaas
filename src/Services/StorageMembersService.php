<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractStorageMembersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for StorageMembers
 *
 * Class StorageMembersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class StorageMembersService extends AbstractStorageMembersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
    public static function getStorageMemberOfComputeMember(ComputeMembers $computeMember) : ?StorageMembers
    {
        //  Here we will check if we have a storage member for this compute member
        $storageMember = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('local_ip_addr', $computeMember->local_ip_addr)
            ->first();

        return $storageMember;
    }
}
