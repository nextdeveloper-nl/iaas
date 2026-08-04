<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Covers the hypervisor-touching steps of VirtualMachines\Backup.php that don't fit the
 * more general-purpose capability interfaces: an implicit (no explicit Repositories row)
 * default backup destination, and stripping a disposable clone's networking before
 * export. See docs/hypervisor-driver-architecture.md.
 */
interface BackupCapableInterface
{
    /**
     * Mounts the account/cloud-node's default backup repository onto the compute member -
     * the destination is resolved internally by the driver, not passed explicitly.
     */
    public function mountDefaultBackupRepository(ComputeMembers $computeMember): array;

    /**
     * Detaches every network card the VM currently has on the hypervisor. Used on a
     * disposable clone right before export - the clone has no VirtualNetworkCards DB rows
     * of its own, so this operates purely on the hypervisor's live VIF list.
     */
    public function stripAllNetworkCards(VirtualMachines $vm): void;

    /**
     * Exports the VM to the account/cloud-node's default backup repository (as opposed to
     * ExportCapableInterface::exportToRepository(), which targets an explicit repository).
     */
    public function exportToDefaultBackupRepository(VirtualMachines $vm): array;

    /**
     * The following methods are deliberately granular (raw XAPI results, not persisted
     * rows) rather than bundled like createSnapshot()/clone() - RunBackupJob.php and
     * InitiateMultilevelBackupJob.php split "do the hypervisor operation" and "persist its
     * DB row" into separately-resumable checkpoints, so a bundled method would break that
     * resumability contract.
     */

    /** Takes a snapshot, returning the raw XAPI result - the caller decides if/when to persist it. */
    public function takeSnapshotRaw(VirtualMachines $vm): array;

    public function fixVmName(VirtualMachines $vm): bool;

    /** Clones a VM, returning the raw XAPI result - the caller decides if/when to persist it. */
    public function cloneVmRaw(VirtualMachines $vm): array;

    /** Mounts an explicit repository to a compute member (distinct from mountRepository()/mountDefaultBackupRepository() - a third, separate XenService method used by this backup flow). */
    public function mountBackupRepository(ComputeMembers $computeMember, Repositories $repository): array;

    /** Returns the export task's progress (0-100) if one is currently running for this VM name, null otherwise. */
    public function isBackupRunning(ComputeMembers $computeMember, string $vmName): ?float;

    public function exportToRepositoryInBackground(VirtualMachines $vm, Repositories $repository, string $exportName, VirtualMachineBackups $vmBackup): bool;
}
