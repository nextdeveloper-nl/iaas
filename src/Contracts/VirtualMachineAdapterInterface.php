<?php

namespace Nextdeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface VirtualMachineAdapterInterface
{
    public function start(VirtualMachines $vm): VirtualMachines;

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines;

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines;

    public function pause(VirtualMachines $vm): VirtualMachines;

    public function resume(VirtualMachines $vm): VirtualMachines;

    public function suspend(VirtualMachines $vm): VirtualMachines;

    public function getHypervisorData(VirtualMachines $vm): array;

    public function delete(VirtualMachines $vm): bool;

    public function sync(VirtualMachines $vm): VirtualMachines;

    public function listAll(): array;
}
