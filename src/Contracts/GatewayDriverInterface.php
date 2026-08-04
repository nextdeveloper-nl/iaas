<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\ValueObjects\GatewayHealthStatus;

/**
 * Mandatory contract every gateway driver must implement, dispatched by
 * GatewayDriverManager keyed off Gateways.gateway_type (see config/gateway_drivers.php).
 * Mirrors VirtualMachineAdapterInterface's role for hypervisors - see
 * docs/hypervisor-driver-architecture.md for the pattern this is copied from.
 */
interface GatewayDriverInterface
{
    /**
     * Runs once, after the gateway's underlying VM has booted, to bring a freshly
     * provisioned appliance into a usable state (retrieve/set admin credentials, enable
     * API access) and persist the result onto the Gateways row (ssh_username/ssh_password/
     * api_token/api_url) via GatewaysService::update().
     */
    public function bootstrap(Gateways $gateway): GatewayHealthStatus;

    /**
     * Cheap reachability/API-auth check, used by the health endpoint and by bootstrap()'s
     * caller to know when the appliance is ready.
     */
    public function healthCheck(Gateways $gateway): GatewayHealthStatus;

    /**
     * Pushes the gateway's gateway_data desired-state config to the live appliance.
     */
    public function applyConfiguration(Gateways $gateway): void;

    /**
     * Best-effort driver-side cleanup before the underlying VM and Gateways row are
     * deleted (e.g. deregistering from a controller, releasing a floating license).
     * Most drivers can no-op this.
     */
    public function teardown(Gateways $gateway): void;
}
