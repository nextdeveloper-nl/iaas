<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface VirtualMachineHandlerInterface
{
    /**
     * Execute the handler logic against the given virtual machine.
     */
    public function handle(VirtualMachines $vm): void;

    /**
     * Whether this handler should run asynchronously as a queued job.
     * Return false to run synchronously (blocking).
     */
    public function isAsync(): bool;
}
