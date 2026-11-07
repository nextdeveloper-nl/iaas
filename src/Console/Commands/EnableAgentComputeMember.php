<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Turns on the xenserver HTTPS agent for a compute member that already exists (the sibling of
 * iaas:add-agent-compute-member, which only creates new members).
 *
 * It takes the member's UUID and the agent bearer token, checks that the agent answers (and reads
 * the operations the host allows) BEFORE anything is written, then writes agent_api_key and
 * management_data.http_agent through ComputeMembersService. Other keys already in management_data
 * are kept.
 *
 * It never changes the pool. Which driver a pool uses is ComputePools.virtualization
 * (xenserver-8.2-agent / xcp-ng-8.2-agent) and it applies to every member of the pool, so switching
 * it is a separate, deliberate step. is_management_agent_available is left alone for the reason
 * given in AgentEndpoint.
 */
class EnableAgentComputeMember extends AddAgentComputeMember
{
    /**
     * @var string
     */
    protected $signature = 'iaas:enable-agent-compute-member
                            {member : UUID of the existing compute member}
                            {token : Agent bearer token (http.api_key on the host, at least 32 characters)}
                            {--port= : Agent HTTPS port (default from config virtualization.agent.port)}
                            {--pin= : Pinned public key of the agent certificate, "sha256//<base64>" (default: read from the host and ask)}
                            {--dry-run : Only check the agent and print what would be written}';

    /**
     * @var string
     */
    protected $description = 'Enables the xenserver HTTPS agent (token + TLS pin) on an existing compute member';

    public function handle(): int
    {
        // Console has no logged-in user, see AddAgentComputeMember.
        UserHelper::setAdminAsCurrentUser();

        $token = (string) $this->argument('token');

        if (!Str::isUuid((string) $this->argument('member'))) {
            $this->error('The member must be the UUID of an existing compute member.');

            return self::FAILURE;
        }

        // The agent refuses shorter keys, so a short token can only be a typo.
        if (strlen($token) < 32) {
            $this->error('The token must be at least 32 characters (the agent rejects shorter keys).');

            return self::FAILURE;
        }

        $member = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $this->argument('member'))
            ->first();

        if (!$member) {
            $this->error('Cannot find the compute member ' . $this->argument('member') . '.');

            return self::FAILURE;
        }

        // Same reason as in AddAgentComputeMember: with SSH_DRY_RUN the client would "pass" without a request.
        if (config('leo.debug.ssh.dry_run')) {
            $this->error('SSH_DRY_RUN is true, so no request would be sent to the agent. ' .
                'Set SSH_DRY_RUN=false for this run and try again.');

            return self::FAILURE;
        }

        // The agent is reached on the same address the driver uses (ip_addr without the mask).
        $ip = explode('/', (string) $member->ip_addr)[0];

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error('Compute member ' . $member->name . ' has no usable ip_addr ("' . $member->ip_addr . '").');

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

        // Keep whatever else lives in management_data, only the http_agent key is ours.
        $managementData              = is_array($member->management_data) ? $member->management_data : [];
        $managementData['http_agent'] = $httpAgent;

        // Pre-flight, before anything is written: an unsaved copy carrying the new settings is enough
        // for the client (it also keeps connection accounting off the real member).
        $probe = new ComputeMembers([
            'name'            => $member->name,
            'ip_addr'         => $member->ip_addr,
            'agent_api_key'   => $token,
            'management_data' => $managementData,
        ]);

        $this->line('Checking the agent at ' . $ip . ' ...');

        try {
            $operations = $this->agentClient($probe)->call('agent.allowed_operations');
            $hosts      = $this->agentClient($probe)->call('xenserver.host.list');
        } catch (XenAgentException $e) {
            $this->error('The agent check failed, nothing was changed: ' . $e->getMessage());
            $this->line('Hint: check the token, the port and the certificate pin.');

            return self::FAILURE;
        }

        // Real reply shape: {operations: [{operation, description, params}, ...], exec_commands: [...]}.
        $list      = $operations['operations'] ?? $operations;
        $available = array_values(array_filter($list, fn($item) => is_array($item) && isset($item['operation'])));

        $this->info('Agent answered. Allowed operations: ' . count($available) . ', hosts reported: ' . count($hosts) . '.');

        if (!empty($member->agent_api_key)) {
            $this->warn('The member already has an agent_api_key (the NATS agent key by default). It will be replaced by this token.');
        }

        // The driver is chosen per POOL, this command does not touch it. Say what happens next.
        $pool = ComputePools::withoutGlobalScope(AuthorizationScope::class)->where('id', $member->iaas_compute_pool_id)->first();

        if ($pool && !str_ends_with((string) $pool->virtualization, '-agent')) {
            $this->warn('Pool "' . $pool->name . '" uses virtualization "' . $pool->virtualization . '", so its actions keep using ' .
                'that driver (SSH) for this member. Switch the pool to an "-agent" key on purpose, and only when every ' .
                'member of the pool runs the agent.');
        } elseif ($pool) {
            $this->line('Pool "' . $pool->name . '" already uses "' . $pool->virtualization . '".');
        }

        if ($this->option('dry-run')) {
            $this->line('Dry run: would enable the agent on member "' . $member->name . '" (' . $member->uuid . ').');

            return self::SUCCESS;
        }

        ComputeMembersService::update($member->uuid, array_filter([
            'agent_api_key'        => $token,
            'management_data'      => $managementData,
            // Keep operation lists of other sources, only the agent one is replaced.
            'available_operations' => $available
                ? array_merge(is_array($member->available_operations) ? $member->available_operations : [], ['agent' => $available])
                : null,
        ], fn($value) => $value !== null));

        $this->info('Agent enabled on compute member ' . $member->name . ' (' . $member->uuid . ').');

        return self::SUCCESS;
    }
}
