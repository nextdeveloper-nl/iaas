<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface ResizeCapableInterface
{
    /**
     * Changes a running or halted VM's vCPU count and/or memory. $corePerSocket, when
     * given, sets the core-per-socket topology (some hypervisors reject certain
     * core counts unless this is set consistently).
     */
    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines;
}
