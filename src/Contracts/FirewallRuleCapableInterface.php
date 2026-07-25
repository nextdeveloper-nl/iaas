<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\ValueObjects\GatewayFirewallRule;

/**
 * Optional capability - a gateway driver implements this only if its backend supports
 * firewall-rule management through our API. Callers `instanceof`-check before invoking,
 * same convention as SnapshotCapableInterface/CloneCapableInterface for hypervisors.
 */
interface FirewallRuleCapableInterface
{
    /** @return GatewayFirewallRule[] */
    public function listRules(Gateways $gateway): array;

    public function createRule(Gateways $gateway, array $rule): GatewayFirewallRule;

    public function deleteRule(Gateways $gateway, string $ruleRef): bool;
}
