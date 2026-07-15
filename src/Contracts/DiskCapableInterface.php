<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface DiskCapableInterface
{
    public function createDisk(VirtualDiskImages $vdi): VirtualDiskImages;

    public function attachDisk(VirtualDiskImages $vdi): VirtualDiskImages;

    public function detachDisk(VirtualDiskImages $vdi): VirtualDiskImages;

    public function destroyDisk(VirtualDiskImages $vdi): bool;

    public function resizeDisk(VirtualDiskImages $vdi, int $sizeInBytes): VirtualDiskImages;

    public function mountCd(VirtualMachines $vm, RepositoryImages $image): bool;

    public function unmountCd(VirtualMachines $vm): bool;
}
