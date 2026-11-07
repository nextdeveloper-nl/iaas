<?php

namespace NextDeveloper\IAAS\Tests\Services\Hypervisors;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\VirtualMachineAgentSyncService;
use Tests\TestCase;

/**
 * The ownership rule that stops a host from taking over a VM record by reporting its uuid.
 * Pure logic, no database or agent.
 */
class VirtualMachineAgentSyncServiceTest extends TestCase
{
    private function owned(int $memberId, int $memberPoolId, ?int $vmPoolId, ?int $vmMemberId): bool
    {
        $member = new ComputeMembers();
        $member->id = $memberId;
        $member->iaas_compute_pool_id = $memberPoolId;

        $vm = new VirtualMachines();
        $vm->iaas_compute_pool_id   = $vmPoolId;
        $vm->iaas_compute_member_id = $vmMemberId;

        $method = new \ReflectionMethod(VirtualMachineAgentSyncService::class, 'vmOwnedBy');
        $method->setAccessible(true);

        return $method->invoke(null, $member, $vm);
    }

    public function test_a_vm_of_the_members_own_pool_can_be_adopted_even_from_another_member()
    {
        // A VM that moved between members of one pool keeps its uuid.
        $this->assertTrue($this->owned(1, 7, 7, 2));
    }

    public function test_a_vm_of_another_pool_is_refused()
    {
        $this->assertFalse($this->owned(1, 7, 8, 1));
    }

    public function test_a_vm_with_no_pool_is_only_adopted_when_it_already_points_at_this_member()
    {
        $this->assertTrue($this->owned(1, 7, null, 1));
        $this->assertFalse($this->owned(1, 7, null, 2));
        $this->assertFalse($this->owned(1, 7, null, null));
    }
}
