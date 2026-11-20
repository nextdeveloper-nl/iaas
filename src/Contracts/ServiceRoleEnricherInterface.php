<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Adds platform-supplied values to one service role's config while a virtual machine's
 * configuration ISO is being built.
 *
 * Some roles need a value the customer cannot type and that must not be stored, for
 * example a one-time registration token issued by another module. IAAS does not know
 * those modules, so the application registers an enricher for the role
 * (see ServiceRoleEnrichers::register()).
 */
interface ServiceRoleEnricherInterface
{
    /**
     * @param array<string, mixed> $config the role's config as the customer saved it
     * @return array<string, mixed> keys to add to that config for this ISO build only;
     *                              an empty array adds nothing
     */
    public function enrich(VirtualMachines $vm, array $config): array;
}
