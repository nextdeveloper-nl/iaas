<?php

namespace NextDeveloper\IAAS\Jobs\Nats;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Jobs\AbstractAgentEventJob;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Dispatched by NatsListenCommand for every message received on agent.vm.*.evt.
 * Replaces the old always-on `iaas:vm-agent-listen` command with the shared
 * queued-Job listener pattern (see AbstractAgentEventJob).
 */
class HandleVmAgentEventJob extends AbstractAgentEventJob
{
    // Thresholds — percentages above which a warning is raised
    private const THRESHOLD_CPU_PCT     = 90.0;
    private const THRESHOLD_MEMORY_PCT  = 90.0;
    private const THRESHOLD_DISK_PCT    = 85.0;
    private const THRESHOLD_DISK_IO_PCT = 90.0;

    protected function resolveAgentModel(string $agentUuid)
    {
        return VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $agentUuid)
            ->first();
    }

    protected function updateHeartbeat($model, array $payload): void
    {
        $timestamp = $payload['timestamp'] ?? null;
        $pingTime  = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : now();

        VirtualMachinesService::update($model->uuid, ['agent_latest_ping' => $pingTime]);

        // If the agent capabilities are not yet known, request them
        $agentOps = ($model->available_operations ?? [])['agent'] ?? [];

        if (empty($agentOps)) {
            $model->sendAgentCommand('agent.allowed_operations', [], 10);
        }
    }

    protected function updateCapabilities($model, array $operations): void
    {
        // Merge into the existing map — only overwrite the 'agent' key so that
        // other sources (e.g. 'hypervisor') are not affected.
        $existing          = $model->available_operations ?? [];
        $existing['agent'] = $operations;

        VirtualMachinesService::update($model->uuid, ['available_operations' => $existing]);
    }

    protected function handleDomainEvent(string $type, $model, array $payload): void
    {
        if ($type !== 'telemetry') {
            Log::warning('[HandleVmAgentEventJob] Unhandled message type', [
                'type'       => $type,
                'agent_uuid' => $model->uuid,
            ]);
            return;
        }

        $this->evaluateHealth($model, $payload);
    }

    private function evaluateHealth(VirtualMachines $vm, array $data): void
    {
        $problems = [];

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

        Log::warning('[HandleVmAgentEventJob] VM health problem detected', [
            'agent_uuid' => $vm->uuid,
            'problems'   => $problems,
        ]);

        foreach ($problems as $problem) {
            CommentsService::createSystemComment($problem, $vm);
        }
    }
}
