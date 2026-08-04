<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface ExportCapableInterface
{
    /**
     * Exports a (normally halted) VM to the given repository as a portable machine
     * image/template. Returns the driver-native identifier (e.g. XenServer's exported
     * template UUID) the caller uses to build the resulting filename/path for a
     * RepositoryImages record - this does not create that record itself.
     */
    public function exportToRepository(VirtualMachines $vm, Repositories $repository): string;
}
