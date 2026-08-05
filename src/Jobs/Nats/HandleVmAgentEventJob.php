<?php

namespace NextDeveloper\IAAS\Jobs\Nats;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Database\Models\AgentCommands;
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
    // Dedicated queue — heartbeat+telemetry arrives every ~30s per VM across the
    // whole fleet and must not share a worker with unrelated platform traffic on
    // the default queue (same fix already applied to S3/backup agent events -
    // see HandleS3AgentEventJob - after that traffic starved the shared 'default'
    // workers and jobs sat past retry_after without ever running). This was the
    // actual cause of the agent_latest_ping investigation: jobs were being
    // dispatched fine but never dequeued, so handle() never ran and nothing
    // ever got logged.
    public $queue = 'vm-agent-events';

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

    // Distinguishes, for the 'unknown agent_uuid' log line, between a VM
    // that's soft-deleted (agent kept running past teardown - expected, not
    // a bug) and a UUID that matches no VM at all (agent identity mismatch,
    // e.g. a clone that never picked up its own config-drive - see the
    // agent_latest_ping investigation this was added for).
    protected function diagnoseUnknownAgent(string $agentUuid): array
    {
        $trashed = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->where('uuid', $agentUuid)
            ->first();

        if (!$trashed) {
            return [
                'reason' => 'no_vm_with_this_uuid',
                'agent_uuid' => $agentUuid
            ];
        }

        return [
            'reason'          => 'vm_is_soft_deleted',
            'vm_id'           => $trashed->id,
            'vm_name'         => $trashed->name,
            'deleted_at'      => optional($trashed->deleted_at)->toIso8601String(),
            'iam_account_id'  => $trashed->iam_account_id,
        ];
    }

    protected function updateHeartbeat($model, array $payload): void
    {
        $timestamp = $payload['timestamp'] ?? null;
        $pingTime  = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : now();

        // Explicit before/after trace around the actual write - the outer
        // AbstractAgentEventJob::handle() only logs on a thrown exception, so
        // without this line a heartbeat that resolves fine but silently fails
        // to persist (or succeeds) leaves no evidence either way.
        Log::info('[HandleVmAgentEventJob] updating agent_latest_ping', [
            'vm_id'        => $model->id,
            'vm_uuid'      => $model->uuid,
            'previous_ping'=> optional($model->agent_latest_ping)->toIso8601String(),
            'new_ping'     => $pingTime->toIso8601String(),
        ]);

        VirtualMachinesService::update($model->uuid, ['agent_latest_ping' => $pingTime]);

        Log::info('[HandleVmAgentEventJob] agent_latest_ping update call completed', [
            'vm_uuid' => $model->uuid,
        ]);

        // If the agent capabilities are not yet known, request them
        $agentOps = ($model->available_operations ?? [])['agent'] ?? [];

        if (empty($agentOps)) {
            $model->sendAgentCommand('agent.allowed_operations', [], 10);
        }

        // Once capabilities are known and the agent supports reporting its version,
        // request it — but only until we actually have one recorded, so this doesn't
        // re-fire on every heartbeat (see the queue-flood incidents this project has
        // already hit with other self-healing loops).
        $hasVersion = !empty(($model->features ?? [])['agent_version']);

        // $agentOps holds capability objects ({operation, description, params}),
        // not plain strings - check the 'operation' column, same fix as
        // assertAgentOperationAllowed() in the VirtualMachines model.
        if (!$hasVersion && in_array('agent.version', array_column($agentOps, 'operation'), true)) {
            $model->sendAgentCommand('agent.version', [], 10);
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

    protected function onCommandResult($model, ?AgentCommands $command, array $payload): void
    {
        if (!$model || !$command || $command->operation !== 'agent.version') {
            return;
        }

        $version = $command->result['version'] ?? $payload['output']['version'] ?? null;

        if (!$version) {
            Log::warning('[HandleVmAgentEventJob] agent.version result had no version', [
                'agent_uuid' => $model->uuid,
                'payload'    => $payload,
            ]);
            return;
        }

        VirtualMachinesService::recordAgentVersion($model, $version);
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
