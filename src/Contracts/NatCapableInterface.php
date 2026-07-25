<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\ValueObjects\GatewayPortForward;

/**
 * Optional capability - a gateway driver implements this only if its backend supports
 * NAT/port-forward management through our API. Callers `instanceof`-check before
 * invoking, same convention as SnapshotCapableInterface/CloneCapableInterface for
 * hypervisors.
 */
interface NatCapableInterface
{
    /** @return GatewayPortForward[] */
    public function listPortForwards(Gateways $gateway): array;

    public function createPortForward(Gateways $gateway, array $forward): GatewayPortForward;

    public function deletePortForward(Gateways $gateway, string $forwardRef): bool;
}
