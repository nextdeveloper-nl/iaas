<?php

namespace NextDeveloper\IAAS\Tests\Services\Hypervisors;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\VirtualMachineAgentSyncService;
use Tests\TestCase;

/**
 * The pool-ownership rule that stops a host from taking over records of another compute pool by
 * reporting their uuid. Pure logic, no database or agent.
 */
class VirtualMachineAgentSyncServiceTest extends TestCase
{
    private function inPoolOf(int $memberPoolId, $rowPoolId): bool
    {
        $member = new ComputeMembers();
        $member->iaas_compute_pool_id = $memberPoolId;

        $method = new \ReflectionMethod(VirtualMachineAgentSyncService::class, 'inPoolOf');
        $method->setAccessible(true);

        return $method->invoke(null, $member, $rowPoolId);
    }

    public function test_a_row_of_the_members_own_pool_can_be_adopted()
    {
        $this->assertTrue($this->inPoolOf(7, 7));
        $this->assertTrue($this->inPoolOf(7, '7'));
    }

    public function test_a_row_of_another_pool_is_refused()
    {
        $this->assertFalse($this->inPoolOf(7, 8));
    }

    public function test_a_row_with_no_pool_yet_can_be_adopted()
    {
        $this->assertTrue($this->inPoolOf(7, null));
    }
}
