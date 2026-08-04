<?php

namespace NextDeveloper\IAAS\ValueObjects;

/**
 * Normalized reachability/auth result returned by GatewayDriverInterface::healthCheck()
 * and ::bootstrap(). $reachable is network-level (ping/port open); $apiAuthOk is
 * application-level (credentials/API token actually work) - a gateway can be reachable
 * but not yet bootstrapped, or bootstrapped but temporarily unreachable, so these are
 * tracked separately rather than collapsed into one boolean.
 */
final class GatewayHealthStatus
{
    public function __construct(
        public readonly bool $reachable,
        public readonly bool $apiAuthOk,
        public readonly ?string $message = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'reachable' => $this->reachable,
            'api_auth_ok' => $this->apiAuthOk,
            'message' => $this->message,
        ];
    }
}
