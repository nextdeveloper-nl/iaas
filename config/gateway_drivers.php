<?php

use NextDeveloper\IAAS\Services\Hypervisors\PfSense\PfSenseGatewayDriver;

/**
 * Maps a Gateways.gateway_type value to the driver class that implements it - mirrors
 * config/virtualization.php's role for hypervisors. See
 * docs/hypervisor-driver-architecture.md for the pattern this is copied from.
 */
return [
    'platforms' => [
        'pfsense' => [
            'driver' => PfSenseGatewayDriver::class,
        ],
    ],
];
