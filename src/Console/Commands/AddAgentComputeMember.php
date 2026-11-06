<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentClient;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Adds a compute member that is managed through the xenserver HTTPS agent to an existing
 * compute pool.
 *
 * It takes the member's IP, the agent bearer token and the pool UUID, checks that the agent
 * answers (and reads the operations the host allows) BEFORE anything is written, then creates
 * the member through ComputeMembersService with management_data.http_agent.enabled=true.
 * It never creates or changes a pool.
 *
 * TLS: the agent serves a self-signed certificate, so the connection is pinned to the agent's
 * public key. Without --pin the command reads the key from the host and asks you to confirm it
 * (like the first SSH connection to a host). The pin is a public-key hash, not a secret, so it
 * lives in management_data, which the API returns.
 *
 * is_management_agent_available is deliberately left alone: legacy services route it to an
 * empty stub, see AgentEndpoint.
 */
class AddAgentComputeMember extends Command
{
    /**
     * @var string
     */
    protected $signature = 'iaas:add-agent-compute-member
                            {ip : IP address of the hypervisor host (no mask)}
                            {token : Agent bearer token (http.api_key on the host, at least 32 characters)}
                            {--pool= : UUID of the compute pool the member is added to}
                            {--name= : Compute member name (default: agent-<ip>)}
                            {--port= : Agent HTTPS port (default from config virtualization.agent.port)}
                            {--pin= : Pinned public key of the agent certificate, "sha256//<base64>" (default: read from the host and ask)}
                            {--dry-run : Only check the agent and print what would be created}';

    /**
     * @var string
     */
    protected $description = 'Adds a compute member that is managed by the xenserver HTTPS agent (ip + token) to a pool';

    public function handle(): int
    {
        // Console has no logged-in user. leo.current_user_id is not set in this environment
        // (setUserById would throw), so run as the system user of the Leo owner account.
        // ComputeMembersService::create takes iam_user_id/iam_account_id from this context.
        UserHelper::setAdminAsCurrentUser();

        $ip    = (string) $this->argument('ip');
        $token = (string) $this->argument('token');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error('"' . $ip . '" is not a valid IP address (give the bare address, without a mask).');

            return self::FAILURE;
        }

        // The agent refuses shorter keys, so a short token can only be a typo.
        if (strlen($token) < 32) {
            $this->error('The token must be at least 32 characters (the agent rejects shorter keys).');

            return self::FAILURE;
        }

        if (!Str::isUuid((string) $this->option('pool'))) {
            $this->error('--pool=<uuid> is required: the UUID of the compute pool to add the member to.');

            return self::FAILURE;
        }

        $pool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $this->option('pool'))
            ->first();

        if (!$pool) {
            $this->error('Cannot find the compute pool ' . $this->option('pool') . '.');

            return self::FAILURE;
        }

        $existing = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('ip_addr', 'like', $ip . '%')
            ->first();

        if ($existing) {
            $this->error('A compute member with this IP already exists: ' . $existing->name . ' (' . $existing->uuid . ').');

            return self::FAILURE;
        }

        // With SSH_DRY_RUN=true the client sends nothing and returns ['dry_run' => true], which
        // would make the check below "pass" without ever reaching the host.
        if (config('leo.debug.ssh.dry_run')) {
            $this->error('SSH_DRY_RUN is true, so no request would be sent to the agent. ' .
                'Set SSH_DRY_RUN=false for this run and try again.');

            return self::FAILURE;
        }

        $port = $this->option('port') ? (int) $this->option('port') : (int) config('virtualization.agent.port', 8443);
        $pin  = $this->option('pin') ?: $this->fetchPin($ip, $port);

        if (!$pin) {
            return self::FAILURE;
        }

        $httpAgent = array_filter([
            'enabled'           => true,
            'port'              => $this->option('port') ? $port : null,
            'pinned_public_key' => $pin,
        ], fn($value) => $value !== null);

        $name = $this->option('name') ?: 'agent-' . $ip;

        // Pre-flight, before anything is written: an unsaved member is enough for the client.
        $probe = new ComputeMembers([
            'name'            => $name,
            'ip_addr'         => $ip,
            'agent_api_key'   => $token,
            'management_data' => ['http_agent' => $httpAgent],
        ]);

        $this->line('Checking the agent at ' . $ip . ' ...');

        try {
            $operations = $this->agentClient($probe)->call('agent.allowed_operations');
            $hosts      = $this->agentClient($probe)->call('xenserver.host.list');
        } catch (XenAgentException $e) {
            $this->error('The agent check failed, nothing was created: ' . $e->getMessage());
            $this->line('Hint: check the token, the port and the certificate pin.');

            return self::FAILURE;
        }

        // Real reply shape (checked against a live agent): {operations: [{operation, description,
        // params}, ...], exec_commands: [...]}. Fall back to a bare list for older agents.
        $list      = $operations['operations'] ?? $operations;
        $available = array_values(array_filter($list, fn($item) => is_array($item) && isset($item['operation'])));

        $this->info('Agent answered. Allowed operations: ' . count($available) . ', hosts reported: ' . count($hosts) . '.');

        // The driver is chosen per POOL (ComputePools.virtualization). This command does not
        // change it, so tell the operator which transport the pool's actions will really use.
        if (!str_ends_with((string) $pool->virtualization, '-agent')) {
            $this->warn('Pool "' . $pool->name . '" uses virtualization "' . $pool->virtualization . '", so actions ' .
                'that resolve the driver from the pool will use that driver for this member, not the agent.');
        }

        if ($this->option('dry-run')) {
            $this->line('Dry run: would add member "' . $name . '" (' . $ip . ') to pool ' . $pool->name . '.');

            return self::SUCCESS;
        }

        $member = ComputeMembersService::create(array_filter([
            'name'                  => $name,
            'ip_addr'               => $ip,
            'iaas_compute_pool_id'  => $pool->uuid,
            'agent_api_key'         => $token,
            'management_data'       => ['http_agent' => $httpAgent],
            'available_operations'  => $available ? ['agent' => $available] : null,
            'is_alive'              => true,
        ], fn($value) => $value !== null));

        $this->info('Compute member created: ' . $member->name . ' (' . $member->uuid . ') in pool ' . $pool->name . '.');

        return self::SUCCESS;
    }

    /**
     * Reads the agent's public key over a TLS handshake that skips verification (the cert is
     * self-signed), prints the pin and asks the operator to accept it - trust on first use, the
     * same model as the first SSH connection to a host. Nothing is sent over the connection.
     * Returns "sha256//<base64>" (the SubjectPublicKeyInfo hash curl pins on) or null.
     */
    private function fetchPin(string $ip, int $port): ?string
    {
        $context = stream_context_create(['ssl' => [
            'capture_peer_cert' => true,
            'verify_peer'       => false,
            'verify_peer_name'  => false,
        ]]);

        $stream = @stream_socket_client(
            'ssl://' . (str_contains($ip, ':') ? '[' . $ip . ']' : $ip) . ':' . $port,
            $errno,
            $error,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$stream) {
            $this->error('Cannot connect to ' . $ip . ':' . $port . ' to read the certificate: ' . $error);

            return null;
        }

        $certificate = stream_context_get_params($stream)['options']['ssl']['peer_certificate'] ?? null;
        fclose($stream);

        $details = $certificate ? openssl_pkey_get_details(openssl_pkey_get_public($certificate)) : null;

        if (!$details || empty($details['key'])) {
            $this->error('Could not read the public key from the certificate of ' . $ip . '.');

            return null;
        }

        // The PEM "PUBLIC KEY" body is the DER-encoded SubjectPublicKeyInfo.
        $der = base64_decode(preg_replace('/-----[A-Z ]+-----|\s/', '', $details['key']));
        $pin = 'sha256//' . base64_encode(hash('sha256', $der, true));

        $subject = openssl_x509_parse($certificate)['name'] ?? 'unknown';

        $this->line('Agent certificate subject: ' . $subject);
        $this->line('Agent public key pin: ' . $pin);

        if (!$this->confirm('Trust this key for ' . $ip . '? (compare it with the agent host if unsure)', false)) {
            $this->error('Not trusted, nothing was created.');

            return null;
        }

        return $pin;
    }

    /**
     * Client for the unsaved pre-flight member. Connection accounting is switched off: it writes
     * state rows, and this member does not exist in the database yet.
     */
    private function agentClient(ComputeMembers $probe): XenAgentClient
    {
        return new class($probe) extends XenAgentClient {
            protected function recordConnectionOk(): void
            {
            }

            protected function recordConnectionProblem(string $operation, string $error): void
            {
            }
        };
    }
}
