<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Covers the hypervisor-touching steps of VirtualMachines\Commit.php: importing a draft
 * VM from a repository image, and reconciling its disk/network config against the
 * hypervisor's live state after import. See docs/hypervisor-driver-architecture.md.
 */
interface ProvisioningCapableInterface
{
    public function mountRepository(ComputeMembers $computeMember, Repositories $repository): bool;

    public function unmountRepository(ComputeMembers $computeMember, Repositories $repository): bool;

    /**
     * Mounts an ISO/CD-image repository onto the compute member (distinct from
     * mountRepository(), which mounts a VM-image repository).
     */
    public function mountIsoRepository(ComputeMembers $computeMember, Repositories $repository): bool;

    /**
     * Imports a virtual machine onto the compute member/storage volume from the given
     * repository image, returning the hypervisor-native VM reference/uuid.
     */
    public function importFromImage(
        VirtualMachines $vm,
        ComputeMembers $computeMember,
        Repositories $repository,
        StorageVolumes $volume,
        RepositoryImages $image,
        bool $isLazyDeploy
    ): string;

    /**
     * Fetches the hypervisor's VM parameters by its native reference/uuid - used right
     * after import, before the VM's own hypervisor-keyed lookups are reliable.
     */
    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array;

    /**
     * Sets the hypervisor-side display name of the VM to match our internal uuid.
     */
    public function renameVirtualMachine(VirtualMachines $vm): bool;

    /**
     * Injects a key/value pair into the guest's metadata channel (XenServer: xenstore-data)
     * so in-guest tooling can read it before boot.
     */
    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool;

    /**
     * Reconciles the VM's disk configuration against the hypervisor's live disk list:
     * syncs matched disks, destroys unmatched hypervisor-side disks/cdroms, creates and
     * attaches any draft disk rows.
     */
    public function reconcileDiskConfiguration(VirtualMachines $vm): void;

    /**
     * Reconciles the VM's network card configuration against the hypervisor's live VIF
     * list: syncs matched VIFs, destroys unmatched hypervisor-side VIFs, attaches any
     * draft VirtualNetworkCards rows.
     */
    public function reconcileNetworkConfiguration(VirtualMachines $vm): void;
}
