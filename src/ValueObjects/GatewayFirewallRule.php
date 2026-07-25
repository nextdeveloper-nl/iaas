<?php

namespace NextDeveloper\IAAS\ValueObjects;

/**
 * Driver-agnostic shape for a firewall rule. $ref is the driver's own identifier for the
 * rule (e.g. pfSense's rule tracking id) - callers pass it back to deleteRule() untouched;
 * $extra carries whatever backend-specific fields don't map onto the common shape.
 */
final class GatewayFirewallRule
{
    public function __construct(
        public readonly string $ref,
        public readonly string $action,
        public readonly string $protocol,
        public readonly ?string $source = null,
        public readonly ?string $destination = null,
        public readonly ?string $port = null,
        public readonly ?string $description = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'ref' => $this->ref,
            'action' => $this->action,
            'protocol' => $this->protocol,
            'source' => $this->source,
            'destination' => $this->destination,
            'port' => $this->port,
            'description' => $this->description,
        ];
    }
}
