<?php

namespace NextDeveloper\IAAS\Services\Hypervisors;

use NextDeveloper\IAAS\Contracts\GatewayDriverInterface;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Exceptions\AdapterNotFoundException;

/**
 * Resolves and dispatches to the gateway driver registered for a given
 * Gateways.gateway_type value (see config/gateway_drivers.php). This is the single place
 * Actions/Jobs/Services go through instead of instantiating a concrete driver (e.g.
 * PfSenseGatewayDriver) directly - structural mirror of VirtualMachineManager, see
 * docs/hypervisor-driver-architecture.md for the pattern this is copied from.
 */
class GatewayDriverManager
{
    private array $adapters = [];

    /**
     * Register the driver class for a gateway_type string. Called once per configured
     * platform from IAASServiceProvider, driven by config/gateway_drivers.php.
     */
    public function registerAdapter(string $gatewayType, string $driverClass): void
    {
        $this->adapters[$gatewayType] = $driverClass;
    }

    /**
     * Resolves the driver for a gateway via its gateway_type. Returns null (rather than
     * throwing) when the gateway has no gateway_type or no driver is registered for it -
     * callers `instanceof`-check the result for optional capability interfaces. Use
     * requireAdapter() instead when a missing driver should be a hard failure.
     */
    public function getAdapter(Gateways $gateway): ?GatewayDriverInterface
    {
        if (!$gateway->gateway_type) {
            return null;
        }

        try {
            return $this->resolveDriver($gateway->gateway_type);
        } catch (AdapterNotFoundException) {
            return null;
        }
    }

    /**
     * Same as getAdapter(), but throws instead of returning null - for callers that have
     * no fallback path and need a driver to proceed at all.
     */
    public function requireAdapter(Gateways $gateway): GatewayDriverInterface
    {
        $driver = $this->getAdapter($gateway);

        if (!$driver) {
            throw new AdapterNotFoundException("No driver could be resolved for gateway {$gateway->uuid} - check it has a gateway_type with a registered driver.");
        }

        return $driver;
    }

    /**
     * Resolves the driver instance registered for a given gateway_type string. This is
     * the core resolution primitive.
     */
    public function resolveDriver(string $gatewayType): GatewayDriverInterface
    {
        if (!isset($this->adapters[$gatewayType])) {
            throw new AdapterNotFoundException("No driver registered for gateway type: {$gatewayType}");
        }

        $config = config('gateway_drivers.platforms', [])[$gatewayType] ?? null;

        if (!$config) {
            throw new AdapterNotFoundException("No configuration found for gateway type: {$gatewayType}");
        }

        return app($this->adapters[$gatewayType], [
            'config' => $config,
        ]);
    }
}
