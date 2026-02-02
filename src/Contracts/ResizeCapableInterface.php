<?php

namespace Nextdeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface ResizeCapableInterface
{
    public function resize(VirtualMachines $vm, string $newName, array $options = []): VirtualMachines;
}
