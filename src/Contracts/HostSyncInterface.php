<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;

interface HostSyncInterface
{
    /**
     * Discovers the hypervisor product+version running on this host. Populates
     * ComputeMembers.hypervisor_model for display/diagnostics only - this is never
     * read for dispatch, dispatch always goes through ComputePools.virtualization.
     */
    public function detectVersion(ComputeMembers $computeMember): string;

    public function syncMember(ComputeMembers $computeMember): ComputeMembers;

    public function syncInterfaces(ComputeMembers $computeMember): ComputeMembers;

    public function syncNetworks(ComputeMembers $computeMember): ComputeMembers;

    public function syncStorageVolumes(ComputeMembers $computeMember): ComputeMembers;

    public function syncVirtualMachines(ComputeMembers $computeMember): ComputeMembers;

    /**
     * Whether this host is joined to a real hypervisor-native pool (e.g. XenServer/XCP-ng
     * pool-join), as opposed to only belonging to the platform's own virtual/logical
     * ComputePools grouping. Both are real, coexisting deployment shapes - callers must
     * not assume every host eventually joins a real pool.
     */
    public function isPoolMember(ComputeMembers $computeMember): bool;
}
