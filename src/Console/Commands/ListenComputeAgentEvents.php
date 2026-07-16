<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\AgentCommandsService;
use NextDeveloper\Events\Services\NatsService;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Subscribes to agent.compute.> - the XenServer host agent (xenserver.agent,
 * dom0-resident, xe/xapi-backed) - and evaluates incoming telemetry for health
 * problems. Mirrors ListenVmAgentEvents (same generic agent.{type}.{uuid}.cmd/evt
 * protocol, agent_type = 'compute' instead of 'vm').
 *
 * xapi_event and ipmi message types are logged only, not processed: xapi_event
 * duplicates ground already covered by the existing SSH-based
 * ComputeComputeMemberEventsJob/XenServerEventTranslator pipeline (see
 * docs/hypervisor-driver-architecture.md §5), and ipmi has no existing
 * sensor-alerting home in this codebase yet - both are deliberately deferred.
 *
 * Usage:
 *   php artisan iaas:compute-agent-listen
 *
 * Requires NATS_ENABLED=true in .env.
 */
class ListenComputeAgentEvents extends Command
{
    protected $signature   = 'iaas:compute-agent-listen';
    protected $description = 'Listen to compute member (XenServer host) agent NATS events and alert on health problems';

    private bool $shouldQuit = false;

    private NatsService $nats;

    // Thresholds - percentages above which a warning is raised
    private const THRESHOLD_CPU_PCT     = 90.0;
    private const THRESHOLD_RAM_PCT     = 90.0;
    private const THRESHOLD_STORAGE_PCT = 85.0;

    public function handle(NatsService $nats): int
    {
        if (!config('events.nats.enabled', false)) {
            $this->error('NATS is not enabled. Set NATS_ENABLED=true in your .env file.');
            return 1;
        }

        $this->nats = $nats;

        $this->registerSignalHandlers();

        $nats->subscribe('agent.compute.>', function (array|string $payload, string $subject) {
            Log::info('[ListenComputeAgentEvents] Message received', [
                'subject'    => $subject,
                'type'       => is_array($payload) ? ($payload['type'] ?? 'unknown') : 'non-json',
                'agent_uuid' => is_array($payload) ? ($payload['agent_uuid'] ?? null) : null,
            ]);

            if (!is_array($payload)) {
                Log::warning('[ListenComputeAgentEvents] Non-JSON payload received, skipping', [
                    'subject' => $subject,
                    'raw'     => is_string($payload) ? substr($payload, 0, 200) : gettype($payload),
                ]);
                return;
            }

            match ($payload['type'] ?? '') {
                'telemetry'    => $this->evaluateHealth($payload),
                'heartbeat'    => $this->handleHeartbeat($payload),
                'result'       => $this->handleResult($payload),
                'capabilities' => $this->handleCapabilities($payload),
                'xapi_event', 'ipmi' => Log::info('[ListenComputeAgentEvents] Received but not processed (deferred)', [
                    'type'       => $payload['type'],
                    'agent_uuid' => $payload['agent_uuid'] ?? null,
                ]),
                default        => Log::warning('[ListenComputeAgentEvents] Unhandled message type', [
                    'type'       => $payload['type'] ?? '(missing)',
                    'agent_uuid' => $payload['agent_uuid'] ?? null,
                    'subject'    => $subject,
                ]),
            };
        });

        $this->info('Subscribed to agent.compute.> — evaluating telemetry for health problems. Press Ctrl+C to stop.');

        while (!$this->shouldQuit) {
            try {
                $nats->process(0.1);
            } catch (\Throwable $e) {
                if (str_contains($e->getMessage(), 'Interrupted system call')) {
                    break;
                }
                throw $e;
            }
            pcntl_signal_dispatch();
        }

        $this->info('Listener stopped.');
        return 0;
    }

    private function handleHeartbeat(array $payload): void
    {
        $agentUuid = $payload['agent_uuid'] ?? null;
        $timestamp = $payload['timestamp']  ?? null;

        Log::info('[ListenComputeAgentEvents] Processing heartbeat', [
            'agent_uuid' => $agentUuid,
            'timestamp'  => $timestamp,
        ]);

        if (!$agentUuid) {
            Log::warning('[ListenComputeAgentEvents] Heartbeat missing agent_uuid — full payload follows', [
                'payload' => $payload,
            ]);
            return;
        }

        $computeMember = $this->resolveComputeMember($agentUuid);

        if (!$computeMember) {
            Log::warning('[ListenComputeAgentEvents] ComputeMember not found for heartbeat', [
                'agent_uuid' => $agentUuid,
            ]);
            return;
        }

        $pingTime = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : now();

        UserHelper::runAsAdmin(fn () => ComputeMembersService::update($computeMember->uuid, ['agent_latest_ping' => $pingTime]));

        Log::info('[ListenComputeAgentEvents] ComputeMember heartbeat recorded', [
            'agent_uuid'        => $agentUuid,
            'compute_member_id' => $computeMember->id,
            'agent_latest_ping' => $pingTime->toIso8601String(),
        ]);

        // If the agent capabilities are not yet known, request them
        $agentOps = ($computeMember->available_operations ?? [])['agent'] ?? [];

        Log::info('[ListenComputeAgentEvents] Checking agent capabilities', [
            'agent_uuid'    => $agentUuid,
            'has_agent_ops' => !empty($agentOps),
            'agent_ops'     => $agentOps,
        ]);

        if (empty($agentOps)) {
            $this->nats->publish("agent.compute.{$agentUuid}.cmd", [
                'v'          => 1,
                'id'         => (string) \Illuminate\Support\Str::uuid(),
                'type'       => 'command',
                'agent_type' => 'compute',
                'agent_uuid' => $agentUuid,
                'timestamp'  => time(),
                'payload'    => [
                    'operation' => 'agent.allowed_operations',
                    'params'    => (object) [],
                    'timeout_s' => 10,
                ],
            ]);

            Log::info('[ListenComputeAgentEvents] Requested capabilities from agent', [
                'agent_uuid' => $agentUuid,
                'subject'    => "agent.compute.{$agentUuid}.cmd",
            ]);
        }
    }

    private function handleCapabilities(array $payload): void
    {
        $agentUuid  = $payload['agent_uuid']            ?? null;
        $operations = $payload['payload']['operations'] ?? [];

        if (!$agentUuid) {
            Log::warning('[ListenComputeAgentEvents] capabilities message missing agent_uuid');
            return;
        }

        $computeMember = $this->resolveComputeMember($agentUuid);

        if (!$computeMember) {
            Log::warning('[ListenComputeAgentEvents] ComputeMember not found for capabilities update', ['agent_uuid' => $agentUuid]);
            return;
        }

        // Merge into the existing map — only overwrite the 'agent' key so that
        // other sources (e.g. 'hypervisor') are not affected.
        $existing          = $computeMember->available_operations ?? [];
        $existing['agent'] = $operations;

        UserHelper::runAsAdmin(fn () => ComputeMembersService::update($computeMember->uuid, ['available_operations' => $existing]));

        Log::info('[ListenComputeAgentEvents] ComputeMember capabilities updated', [
            'agent_uuid' => $agentUuid,
            'operations' => $operations,
        ]);
    }

    private function handleResult(array $payload): void
    {
        $result      = $payload['payload'] ?? [];
        $commandUuid = $result['command_id'] ?? null;

        if (!$commandUuid) {
            Log::warning('[ListenComputeAgentEvents] Result message missing command_id', ['payload' => $payload]);
            return;
        }

        $command = AgentCommandsService::getByRef($commandUuid);

        if (!$command) {
            Log::warning('[ListenComputeAgentEvents] Unknown command_id in result', ['command_id' => $commandUuid]);
            return;
        }

        UserHelper::runAsAdmin(fn () => AgentCommandsService::update($command->uuid, [
            'status'       => $result['status']  ?? 'completed',
            'result'       => $result['output']  ?? [],
            'error'        => $result['message'] ?? null,
            'completed_at' => now(),
        ]));

        Log::info('[ListenComputeAgentEvents] Command result received', [
            'command_id' => $commandUuid,
            'status'     => $result['status'] ?? 'completed',
        ]);
    }

    private function evaluateHealth(array $payload): void
    {
        $agentUuid = $payload['agent_uuid'] ?? 'unknown';
        $data      = $payload['payload']    ?? [];
        $problems  = [];

        $cpu = $data['cpu'] ?? [];
        if (isset($cpu['usage_pct']) && $cpu['usage_pct'] >= self::THRESHOLD_CPU_PCT) {
            $problems[] = sprintf('High CPU: %.1f%%', $cpu['usage_pct']);
        }

        $ram = $data['ram'] ?? [];
        if (isset($ram['used_bytes'], $ram['total_bytes']) && $ram['total_bytes'] > 0) {
            $ramPct = ($ram['used_bytes'] / $ram['total_bytes']) * 100;

            if ($ramPct >= self::THRESHOLD_RAM_PCT) {
                $problems[] = sprintf('High memory: %.1f%%', $ramPct);
            }
        }

        foreach (($data['storage']['pools'] ?? []) as $pool) {
            $name = $pool['name'] ?? '?';

            if (isset($pool['used_bytes'], $pool['total_bytes']) && $pool['total_bytes'] > 0) {
                $poolPct = ($pool['used_bytes'] / $pool['total_bytes']) * 100;

                if ($poolPct >= self::THRESHOLD_STORAGE_PCT) {
                    $problems[] = sprintf('High storage usage on pool %s: %.1f%%', $name, $poolPct);
                }
            }
        }

        if (empty($problems)) {
            // Host is healthy — discard
            return;
        }

        Log::warning('[ListenComputeAgentEvents] ComputeMember health problem detected', [
            'agent_uuid' => $agentUuid,
            'problems'   => $problems,
        ]);

        $computeMember = $this->resolveComputeMember($agentUuid);

        if (!$computeMember) {
            Log::warning('[ListenComputeAgentEvents] ComputeMember not found for agent', ['agent_uuid' => $agentUuid]);
            return;
        }

        UserHelper::setAdminAsCurrentUser();

        foreach ($problems as $problem) {
            CommentsService::createSystemComment($problem, $computeMember);
        }
    }

    private function resolveComputeMember(string $agentUuid): ?ComputeMembers
    {
        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $agentUuid)
            ->first();
    }

    private function registerSignalHandlers(): void
    {
        if (!extension_loaded('pcntl')) {
            return;
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, fn () => $this->shouldQuit = true);
        pcntl_signal(SIGINT,  fn () => $this->shouldQuit = true);
    }
}
