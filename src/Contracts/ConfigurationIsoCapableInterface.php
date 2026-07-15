<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

interface ConfigurationIsoCapableInterface
{
    /**
     * Regenerates and re-uploads the VM's cloud-init/config ISO (e.g. after a
     * hostname/network/user-data change), without restarting or starting the VM.
     */
    public function regenerateConfigurationIso(VirtualMachines $vm): bool;
}
