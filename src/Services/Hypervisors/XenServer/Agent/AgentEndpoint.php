<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * Resolves where and how to reach the xenserver HTTPS agent of one compute member.
 *
 * Opt-in is stored in the existing compute_members.management_data JSON column (no DB change):
 *
 *   management_data.http_agent = {
 *       enabled: bool,               // required, host gate of the agent driver
 *       port?: int,                  // default config('virtualization.agent.port')
 *       api_key?: string,            // default compute_members.agent_api_key
 *       pinned_public_key?: string   // curl "sha256//<base64>" pin for the agent's self-signed cert
 *   }
 *
 * is_management_agent_available is intentionally NOT used. Several legacy *XenService classes
 * read it and route to the empty Agentable::performAgentCommand() stub, so turning it on would
 * break every path that still goes over SSH.
 */
class AgentEndpoint
{
    private function __construct(
        public readonly string $baseUrl,
        public readonly string $token,
        public readonly array $httpOptions
    ) {
    }

    /**
     * True when the host has explicitly been switched to the agent transport.
     */
    public static function isEnabled(ComputeMembers $computeMember): bool
    {
        return ($computeMember->management_data['http_agent']['enabled'] ?? false) === true;
    }

    /**
     * @throws XenAgentException when the host is not enabled or has no usable token
     */
    public static function forComputeMember(ComputeMembers $computeMember): self
    {
        if (!self::isEnabled($computeMember)) {
            throw new XenAgentException(
                'The HTTPS agent is not enabled for compute member ' . $computeMember->name .
                ' (management_data.http_agent.enabled is not true).'
            );
        }

        $hostConfig = $computeMember->management_data['http_agent'];

        // ip_addr is stored in CIDR form (10.0.0.5/24) - the agent needs the bare address.
        $host = explode('/', (string) $computeMember->ip_addr)[0];

        if ($host === '') {
            throw new XenAgentException('Compute member ' . $computeMember->name . ' has no ip_addr.');
        }

        $token = $hostConfig['api_key'] ?? $computeMember->agent_api_key;

        if (empty($token)) {
            throw new XenAgentException('Compute member ' . $computeMember->name . ' has no agent api key.');
        }

        $port = (int) ($hostConfig['port'] ?? config('virtualization.agent.port', 8443));

        return new self(
            'https://' . $host . ':' . $port . '/v1/command',
            $token,
            self::httpOptions($hostConfig)
        );
    }

    /**
     * The agent serves a self-signed certificate. Three outcomes, never a silent "verify off":
     *  - host has pinned_public_key: no CA check, but curl refuses any server whose public key
     *    does not match the pin, so the server is still authenticated;
     *  - config virtualization.agent.tls.verify is set (true or a CA bundle path): use it;
     *  - otherwise normal verification, which fails loudly against a self-signed cert.
     */
    private static function httpOptions(array $hostConfig): array
    {
        if (!empty($hostConfig['pinned_public_key'])) {
            return [
                'verify' => false,
                'curl'   => [CURLOPT_PINNEDPUBLICKEY => $hostConfig['pinned_public_key']],
            ];
        }

        return ['verify' => config('virtualization.agent.tls.verify', true)];
    }
}
