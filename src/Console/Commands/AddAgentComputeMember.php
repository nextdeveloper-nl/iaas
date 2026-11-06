<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentClient;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Registers a compute member that is managed through the xenserver HTTPS agent instead of SSH.
 *
 * It takes the member's IP and the agent bearer token, checks that the agent answers (and reads
 * the operations the host allows) BEFORE anything is written, then creates the member through
 * ComputeMembersService with management_data.http_agent.enabled=true.
 *
 * It never changes an existing pool's virtualization (that would move every member of the pool
 * to the agent driver). Give it a pool that already uses an "-agent" platform key with --pool, or
 * let it create a separate, inactive test pool with --like-pool=<uuid> (datacenter and cloud
 * node are copied from that pool).
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
                            {--name= : Compute member name (default: agent-<ip>)}
                            {--pool= : UUID of an existing compute pool whose virtualization is an "-agent" key}
                            {--like-pool= : UUID of a pool to copy datacenter/cloud node from; creates a new inactive agent test pool}
                            {--port= : Agent HTTPS port (default from config virtualization.agent.port)}
                            {--pin= : Pinned public key of the agent certificate, "sha256//<base64>" (needed for the self-signed cert)}
                            {--dry-run : Only check the agent and print what would be created}';

    /**
     * @var string
     */
    protected $description = 'Adds a compute member that is managed by the xenserver HTTPS agent (ip + token)';

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

        if (!$this->option('pool') && !$this->option('like-pool')) {
            $this->error('Give --pool=<uuid> (a pool that already uses an "-agent" platform) or ' .
                '--like-pool=<uuid> to create a separate test pool.');

            return self::FAILURE;
        }

        foreach (['pool', 'like-pool'] as $option) {
            if ($this->option($option) && !\Illuminate\Support\Str::isUuid((string) $this->option($option))) {
                $this->error('--' . $option . ' must be a pool UUID.');

                return self::FAILURE;
            }
        }

        if ($this->option('pool') && $this->option('like-pool')) {
            $this->error('Use either --pool or --like-pool, not both.');

            return self::FAILURE;
        }

        $existing = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('ip_addr', 'like', $ip . '%')
            ->first();

        if ($existing) {
            $this->error('A compute member with this IP already exists: ' . $existing->name . ' (' . $existing->uuid . ').');

            return self::FAILURE;
        }

        $pool = $this->option('pool') ? $this->findAgentPool((string) $this->option('pool')) : null;

        if ($this->option('pool') && !$pool) {
            return self::FAILURE;
        }

        $sourcePool = null;

        if ($this->option('like-pool')) {
            $sourcePool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
                ->where('uuid', $this->option('like-pool'))
                ->first();

            if (!$sourcePool) {
                $this->error('Cannot find the pool given with --like-pool.');

                return self::FAILURE;
            }
        }

        $httpAgent = array_filter([
            'enabled'           => true,
            'port'              => $this->option('port') ? (int) $this->option('port') : null,
            'pinned_public_key' => $this->option('pin') ?: null,
        ], fn($value) => $value !== null);

        $name = $this->option('name') ?: 'agent-' . $ip;

        // Pre-flight, before anything is written: an unsaved member is enough for the client.
        $probe = new ComputeMembers([
            'name'            => $name,
            'ip_addr'         => $ip,
            'agent_api_key'   => $token,
            'management_data' => ['http_agent' => $httpAgent],
        ]);

        // With SSH_DRY_RUN=true the client sends nothing and returns ['dry_run' => true], which
        // would make this check "pass" without ever reaching the host.
        if (config('leo.debug.ssh.dry_run')) {
            $this->error('SSH_DRY_RUN is true, so no request would be sent to the agent. ' .
                'Set SSH_DRY_RUN=false for this run and try again.');

            return self::FAILURE;
        }

        $this->line('Checking the agent at ' . $ip . ' ...');

        try {
            $operations = $this->agentClient($probe)->call('agent.allowed_operations');
            $hosts      = $this->agentClient($probe)->call('xenserver.host.list');
        } catch (XenAgentException $e) {
            $this->error('The agent check failed, nothing was created: ' . $e->getMessage());
            $this->line('Hint: for the self-signed certificate pass --pin="sha256//<base64>" ' .
                '(or set virtualization.agent.tls.verify), and check the token and port.');

            return self::FAILURE;
        }

        // Only keep the capability list if it has the expected {operation, ...} shape.
        $available = array_values(array_filter($operations, fn($item) => is_array($item) && isset($item['operation'])));

        $this->info('Agent answered. Allowed operations: ' . count($available) . ', hosts reported: ' . count($hosts) . '.');

        if ($this->option('dry-run')) {
            $this->line('Dry run: would create member "' . $name . '" (' . $ip . ') in ' .
                ($pool ? 'pool ' . $pool->name : 'a new inactive agent pool copied from ' . $sourcePool->name) . '.');

            return self::SUCCESS;
        }

        if ($sourcePool) {
            $pool = $this->createTestPool($sourcePool, $ip);
            $this->info('Created agent test pool: ' . $pool->name . ' (' . $pool->uuid . '), inactive.');
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
     * Finds the pool and makes sure it already uses an agent platform. Refusing is the safe
     * answer: changing virtualization on a pool would move all of its existing members too.
     */
    private function findAgentPool(string $uuid): ?ComputePools
    {
        $pool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $uuid)
            ->first();

        if (!$pool) {
            $this->error('Cannot find the compute pool ' . $uuid . '.');

            return null;
        }

        if (!str_ends_with((string) $pool->virtualization, '-agent')) {
            $this->error('Pool "' . $pool->name . '" uses virtualization "' . $pool->virtualization . '", not an ' .
                '"-agent" platform. I will not change it, that would move every member of the pool to the agent ' .
                'driver. Use --like-pool=' . $uuid . ' to create a separate test pool instead.');

            return null;
        }

        return $pool;
    }

    /**
     * Separate, inactive pool on the agent platform. is_active=false keeps the scheduler from
     * placing customer VMs on it while it is being tested.
     */
    private function createTestPool(ComputePools $source, string $ip): ComputePools
    {
        $datacenter = $source->iaas_datacenter_id
            ? Datacenters::withoutGlobalScope(AuthorizationScope::class)->where('id', $source->iaas_datacenter_id)->first()
            : null;

        $cloudNode = $source->iaas_cloud_node_id
            ? CloudNodes::withoutGlobalScope(AuthorizationScope::class)->where('id', $source->iaas_cloud_node_id)->first()
            : null;

        return ComputePoolsService::create(array_filter([
            'name'                  => 'agent-test-' . $ip,
            'virtualization'        => 'xenserver-8.2-agent',
            'iaas_datacenter_id'    => $datacenter?->uuid,
            'iaas_cloud_node_id'    => $cloudNode?->uuid,
            'is_active'             => false,
            'is_default'            => false,
            'is_public'             => false,
        ], fn($value) => $value !== null));
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
