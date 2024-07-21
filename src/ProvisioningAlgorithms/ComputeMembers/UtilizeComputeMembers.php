<?php

namespace NextDeveloper\IAAS\ProvisioningAlgorithms\ComputeMembers;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Exceptions\NotEnoughResourcesException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This algorithm tries to fill the most busy compute member first. The reason of this approach is most of the time
 * consume less electric in the datacenter or keep the other compute members as idle as possible, or even in standby
 * mode
 */
class UtilizeComputeMembers extends AbstractComputeMemberAlgorithm
{
    /**
     * This function will calculate the best compute member for the given resources
     *
     * @param integer $cpu
     * @param integer $ram
     * @return mixed
     */
    public function calculate($ram = 0, $cpu = null) : ?ComputeMembers
    {
        /**
         * I could have used a proper SQL or a maybe stored procedure here. But I needed to make it fast.
         * That is why I will revisit here.
         */
        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->orderBy('used_ram', 'desc')
            ->get();

        foreach ($computeMembers as $computeMember) {
            if(
                ($computeMember->total_ram * 1024)
                - ($computeMember->used_ram * 1024)
                >= $ram
            )
                {
                return $computeMember;
            }
        }

        throw new NotEnoughResourcesException('There is not enough resources to allocate this' .
            ' much ram (' . $ram . ' GB) in the pool: '
            . $this->computePool->name . '. You may want to try another pool that is not full.');
    }
}
