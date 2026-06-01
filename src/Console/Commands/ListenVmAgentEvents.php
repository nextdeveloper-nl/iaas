<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\AgentCommandsService;
use NextDeveloper\Events\Services\NatsService;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Subscribes to agent.vm.> and evaluates incoming telemetry for health problems.
 *
 * Telemetry is published directly to vm.{uuid}.telemetry by the Go agent, so
 * this listener does NOT republish. It only reacts when a threshold is breached.
 *
 * Usage:
 *   php artisan iaas:vm-agent-listen
 *
 * Requires NATS_ENABLED=true in .env.
 */
class ListenVmAgentEvents extends Command
{
    protected $signature   = 'iaas:vm-agent-listen';
    protected $description = 'Listen to VM agent NATS events and alert on health problems';

    private bool $shouldQuit = false;

    // Thresholds — percentages above which a warning is raised
    private const THRESHOLD_CPU_PCT     = 90.0;
    private const THRESHOLD_MEMORY_PCT  = 90.0;
    private const THRESHOLD_DISK_PCT    = 85.0;
    private const THRESHOLD_DISK_IO_PCT = 90.0;

    public function handle(NatsService $nats): int
    {
        if (!config('events.nats.enabled', false)) {
            $this->error('NATS is not enabled. Set NATS_ENABLED=true in your .env file.');
            return 1;
        }

        $this->registerSignalHandlers();

        $nats->subscribe('agent.vm.>', function (array|string $payload, string $subject) {
            Log::debug('[ListenVmAgentEvents] Message received', [
                'subject' => $subject,
                'type'    => is_array($payload) ? ($payload['type'] ?? 'unknown') : 'non-json',
                'payload' => $payload,
            ]);

            if (!is_array($payload)) {
                return;
            }

            match ($payload['type'] ?? '') {
                'telemetry'    => $this->evaluateHealth($payload),
                'heartbeat'    => $this->handleHeartbeat($payload),
                'result'       => $this->handleResult($payload),
                'capabilities' => $this->handleCapabilities($payload),
                default        => Log::debug('[ListenVmAgentEvents] Unhandled message type', [
                    'type'    => $payload['type'] ?? 'unknown',
                    'subject' => $subject,
                ]),
            };
        });

        $this->info('Subscribed to agent.vm.> — evaluating telemetry for health problems. Press Ctrl+C to stop.');

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

        if (!$agentUuid) {
            Log::warning('[ListenVmAgentEvents] heartbeat missing agent_uuid');
            return;
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $agentUuid)
            ->first();

        if (!$vm) {
            Log::warning('[ListenVmAgentEvents] VM not found for heartbeat', ['agent_uuid' => $agentUuid]);
            return;
        }

        $pingTime = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : now();

        VirtualMachinesService::update($vm->uuid, ['agent_latest_ping' => $pingTime]);

        Log::debug('[ListenVmAgentEvents] VM heartbeat recorded', [
            'agent_uuid'        => $agentUuid,
            'agent_latest_ping' => $pingTime->toIso8601String(),
        ]);
    }

    private function handleCapabilities(array $payload): void
    {
        $agentUuid  = $payload['agent_uuid']         ?? null;
        $operations = $payload['payload']['operations'] ?? [];

        if (!$agentUuid) {
            Log::warning('[ListenVmAgentEvents] capabilities message missing agent_uuid');
            return;
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $agentUuid)
            ->first();

        if (!$vm) {
            Log::warning('[ListenVmAgentEvents] VM not found for capabilities update', ['agent_uuid' => $agentUuid]);
            return;
        }

        // Merge into the existing map — only overwrite the 'agent' key so that
        // other sources (e.g. 'hypervisor') are not affected.
        $existing           = $vm->available_operations ?? [];
        $existing['agent']  = $operations;

        VirtualMachinesService::update($vm->uuid, ['available_operations' => $existing]);

        Log::info('[ListenVmAgentEvents] VM capabilities updated', [
            'agent_uuid' => $agentUuid,
            'operations' => $operations,
        ]);
    }

    private function handleResult(array $payload): void
    {
        $result      = $payload['payload'] ?? [];
        $commandUuid = $result['command_id'] ?? null;

        if (!$commandUuid) {
            Log::warning('[ListenVmAgentEvents] Result message missing command_id', ['payload' => $payload]);
            return;
        }

        $command = AgentCommandsService::getByRef($commandUuid);

        if (!$command) {
            Log::warning('[ListenVmAgentEvents] Unknown command_id in result', ['command_id' => $commandUuid]);
            return;
        }

        AgentCommandsService::update($command->id, [
            'status'       => $result['status']     ?? 'completed',
            'result'       => $result['output']      ?? [],
            'error'        => $result['message']     ?? null,
            'completed_at' => now(),
        ]);

        Log::info('[ListenVmAgentEvents] Command result received', [
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

        foreach ($cpu['cores'] ?? [] as $core) {
            if (isset($core['usage_pct']) && $core['usage_pct'] >= self::THRESHOLD_CPU_PCT) {
                $problems[] = sprintf('High CPU on core %d: %.1f%%', $core['id'], $core['usage_pct']);
            }
        }

        $memory = $data['memory'] ?? [];
        if (isset($memory['usage_pct']) && $memory['usage_pct'] >= self::THRESHOLD_MEMORY_PCT) {
            $problems[] = sprintf('High memory: %.1f%%', $memory['usage_pct']);
        }

        foreach ($data['disks'] ?? [] as $disk) {
            $label = $disk['mountpoint'] ?? $disk['device'] ?? '?';

            if (isset($disk['usage_pct']) && $disk['usage_pct'] >= self::THRESHOLD_DISK_PCT) {
                $problems[] = sprintf('High disk usage on %s: %.1f%%', $label, $disk['usage_pct']);
            }

            $ioUtil = $disk['io']['util_pct'] ?? null;
            if ($ioUtil !== null && $ioUtil >= self::THRESHOLD_DISK_IO_PCT) {
                $problems[] = sprintf('High disk I/O on %s: %.1f%% utilisation', $label, $ioUtil);
            }
        }

        if (empty($problems)) {
            // VM is healthy — discard
            return;
        }

        Log::warning('[ListenVmAgentEvents] VM health problem detected', [
            'agent_uuid' => $agentUuid,
            'problems'   => $problems,
        ]);

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $agentUuid)
            ->first();

        if (!$vm) {
            Log::warning('[ListenVmAgentEvents] VM not found for agent', ['agent_uuid' => $agentUuid]);
            return;
        }

        UserHelper::setAdminAsCurrentUser();

        foreach ($problems as $problem) {
            CommentsService::createSystemComment($problem, $vm);
        }
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
