<?php

namespace NextDeveloper\IAAS\Jobs\Nats;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Jobs\AbstractAgentEventJob;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Dispatched by NatsListenCommand for every message received on agent.compute.*.evt
 * - the XenServer host agent (xenserver.agent, dom0-resident, xe/xapi-backed).
 * Replaces the old always-on `iaas:compute-agent-listen` command.
 *
 * xapi_event and ipmi message types are logged only, not processed: xapi_event
 * duplicates ground already covered by the existing SSH-based
 * ComputeComputeMemberEventsJob/XenServerEventTranslator pipeline, and ipmi has
 * no existing sensor-alerting home in this codebase yet - both deliberately deferred.
 */
class HandleComputeAgentEventJob extends AbstractAgentEventJob
{
    // Same dedicated queue as HandleVmAgentEventJob - see its comment. Compute
    // members are far lower volume than VMs (a handful of hypervisor hosts vs.
    // the whole VM fleet), so sharing the queue is fine.
    public $queue = 'vm-agent-events';

    // Thresholds - percentages above which a warning is raised
    private const THRESHOLD_CPU_PCT     = 90.0;
    private const THRESHOLD_RAM_PCT     = 90.0;
    private const THRESHOLD_STORAGE_PCT = 85.0;

    protected function resolveAgentModel(string $agentUuid)
    {
        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $agentUuid)
            ->first();
    }

    // See HandleVmAgentEventJob::diagnoseUnknownAgent() - same soft-delete
    // vs. truly-unknown distinction, for the compute-host agent.
    protected function diagnoseUnknownAgent(string $agentUuid): array
    {
        $trashed = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->where('uuid', $agentUuid)
            ->first();

        if (!$trashed) {
            return ['reason' => 'no_compute_member_with_this_uuid'];
        }

        return [
            'reason'         => 'compute_member_is_soft_deleted',
            'cm_id'          => $trashed->id,
            'cm_name'        => $trashed->name ?? null,
            'deleted_at'     => optional($trashed->deleted_at)->toIso8601String(),
        ];
    }

    protected function updateHeartbeat($model, array $payload): void
    {
        $timestamp = $payload['timestamp'] ?? null;
        $pingTime  = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : now();

        ComputeMembersService::update($model->uuid, ['agent_latest_ping' => $pingTime]);

        $agentOps = ($model->available_operations ?? [])['agent'] ?? [];

        if (empty($agentOps)) {
            $model->sendAgentCommand('agent.allowed_operations', [], 10);
        }
    }

    protected function updateCapabilities($model, array $operations): void
    {
        $existing          = $model->available_operations ?? [];
        $existing['agent'] = $operations;

        ComputeMembersService::update($model->uuid, ['available_operations' => $existing]);
    }

    protected function handleDomainEvent(string $type, $model, array $payload): void
    {
        if (in_array($type, ['xapi_event', 'ipmi'], true)) {
            Log::info('[HandleComputeAgentEventJob] Received but not processed (deferred)', [
                'type'       => $type,
                'agent_uuid' => $model->uuid,
            ]);
            return;
        }

        if ($type !== 'telemetry') {
            Log::warning('[HandleComputeAgentEventJob] Unhandled message type', [
                'type'       => $type,
                'agent_uuid' => $model->uuid,
            ]);
            return;
        }

        $this->evaluateHealth($model, $payload);
    }

    private function evaluateHealth(ComputeMembers $computeMember, array $data): void
    {
        $problems = [];

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

        Log::warning('[HandleComputeAgentEventJob] ComputeMember health problem detected', [
            'agent_uuid' => $computeMember->uuid,
            'problems'   => $problems,
        ]);

        foreach ($problems as $problem) {
            CommentsService::createSystemComment($problem, $computeMember);
        }
    }
}
