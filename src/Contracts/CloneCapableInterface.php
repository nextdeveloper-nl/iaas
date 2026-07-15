<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface CloneCapableInterface
{
    public function clone(VirtualMachines $vm, string $newName, array $options = []): VirtualMachines;
}
