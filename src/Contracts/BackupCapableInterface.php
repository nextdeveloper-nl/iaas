<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
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
}
