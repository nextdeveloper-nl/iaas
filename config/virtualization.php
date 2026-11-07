<?php

use NextDeveloper\IAAS\Services\Hypervisors\XenServer\XenServer82AgentDriver;
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
        //  HTTPS agent transport (opt-in per pool by setting ComputePools.virtualization to one
        //  of these, and per host via management_data.http_agent.enabled). No existing row uses
        //  these values, so SSH behavior is unchanged.
        'xenserver-8.2-agent' => [
            'driver' => XenServer82AgentDriver::class,
        ],
        'xcp-ng-8.2-agent' => [
            'driver' => XenServer82AgentDriver::class,
            'product' => 'xcp-ng',
        ],
    ],

    /**
     * Defaults for the xenserver HTTPS agent client (Services/Hypervisors/XenServer/Agent).
     * Per-host overrides (port, api_key, pinned_public_key) live in
     * compute_members.management_data.http_agent - see AgentEndpoint. Nothing here changes the
     * SSH driver; no platform above points at the agent driver yet.
     */
    'agent' => [
        'port'            => (int) env('XEN_AGENT_PORT', 8443),
        'connect_timeout' => (int) env('XEN_AGENT_CONNECT_TIMEOUT', 10),
        'timeout'         => (int) env('XEN_AGENT_TIMEOUT', 60),
        'async' => [
            'poll_interval' => (int) env('XEN_AGENT_POLL_INTERVAL', 3),
            'max_wait'      => (int) env('XEN_AGENT_ASYNC_MAX_WAIT', 3600),
        ],
        'tls' => [
            // true = normal CA verification, or a path to a CA bundle. Hosts that serve the
            // agent's self-signed cert should set management_data.http_agent.pinned_public_key
            // instead of turning verification off here.
            'verify' => env('XEN_AGENT_TLS_VERIFY', true),
        ],
    ],
];
