<?php

namespace Nextdeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface SnapshotCapableInterface
{
    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null): VirtualMachines;

    public function deleteSnapshot(VirtualMachines $vm, string $snapshotId): bool;

    public function restoreSnapshot(VirtualMachines $vm, string $snapshotId): bool;

    public function listSnapshots(VirtualMachines $vm): array;
}
