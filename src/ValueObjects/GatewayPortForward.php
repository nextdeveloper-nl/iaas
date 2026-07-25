<?php

namespace NextDeveloper\IAAS\ValueObjects;

/**
 * Driver-agnostic shape for a NAT port-forward rule. $ref is the driver's own identifier
 * for the rule - callers pass it back to deletePortForward() untouched.
 */
final class GatewayPortForward
{
    public function __construct(
        public readonly string $ref,
        public readonly string $protocol,
        public readonly int $externalPort,
        public readonly string $internalIp,
        public readonly int $internalPort,
        public readonly ?string $description = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'ref' => $this->ref,
            'protocol' => $this->protocol,
            'external_port' => $this->externalPort,
            'internal_ip' => $this->internalIp,
            'internal_port' => $this->internalPort,
            'description' => $this->description,
        ];
    }
}
