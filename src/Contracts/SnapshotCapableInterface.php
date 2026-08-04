<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface SnapshotCapableInterface
{
    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null): VirtualMachines;

    public function deleteSnapshot(VirtualMachines $vm, string $snapshotId): bool;

    public function restoreSnapshot(VirtualMachines $vm, string $snapshotId): bool;

    public function listSnapshots(VirtualMachines $vm): array;

    /**
     * Converts a snapshot (as created by createSnapshot()) into a standalone,
     * independently-usable VM - used by backup flows that clone the resulting VM before
     * releasing the original snapshot.
     */
    public function convertSnapshotToVm(VirtualMachines $vm, ?string $name = null): array;
}
