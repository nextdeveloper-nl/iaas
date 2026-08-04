<?php

use NextDeveloper\IAAS\Services\Hypervisors\XenServer\XenServer82SshDriver;

/**
 * Maps a ComputePools.virtualization value to the driver class that implements it.
 * The virtualization string encodes both the hypervisor product+version and the
 * transport variant (e.g. "xenserver-8.2-ssh" vs a future "xenserver-8.2-agent"),
 * so multiple driver classes can serve the same product depending on how a given
 * deployment is reached - see docs/hypervisor-driver-architecture.md.
 *
 * IMPORTANT: every ComputePools row that exists today has virtualization stored as the
 * bare "xenserver-8.2" (no transport suffix) - that value is registered here as an alias
 * for the same driver so existing data keeps resolving. New ComputePools rows can still
 * be created with the explicit "xenserver-8.2-ssh" form going forward; both mean the
 * same thing today (there is only one XenServer transport implemented so far).
 */
return [
    'platforms' => [
        'xenserver-8.2' => [
            'driver' => XenServer82SshDriver::class,
        ],
        'xenserver-8.2-ssh' => [
            'driver' => XenServer82SshDriver::class,
        ],
        'xcp-ng-8.2' => [
            'driver' => XenServer82SshDriver::class,
            'product' => 'xcp-ng',
        ],
        'xcp-ng-8.2-ssh' => [
            'driver' => XenServer82SshDriver::class,
            'product' => 'xcp-ng',
        ],
    ],
];
