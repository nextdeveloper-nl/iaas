<?php

namespace NextDeveloper\IAAS\Jobs\Nats;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Database\Models\AgentCommands;
use NextDeveloper\Events\Jobs\AbstractAgentEventJob;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\DockerContainersService;
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
        if (!$model || !$command) {
            return;
        }

        if ($command->operation === 'agent.version') {
            $version = $command->result['version'] ?? $payload['output']['version'] ?? null;

            if (!$version) {
                Log::warning('[HandleVmAgentEventJob] agent.version result had no version', [
                    'agent_uuid' => $model->uuid,
                    'payload'    => $payload,
                ]);
                return;
            }

            VirtualMachinesService::recordAgentVersion($model, $version);
            return;
        }

        if (str_starts_with($command->operation, 'docker.')) {
            $this->onDockerCommandResult($command, $payload);
        }
    }

    /**
     * Closes out the DockerContainers row this command was for, once the
     * agent's async result arrives. docker.list/docker.inspect/docker.logs/
     * docker.stats are dispatched synchronously (sendAgentCommandSync) by the
     * controller/read paths and never go through here - only the mutating,
     * async-dispatched (sendAgentCommand) operations from
     * Actions\DockerContainers\{Create,Start,Stop,Restart,Remove} land here.
     */
    private function onDockerCommandResult(AgentCommands $command, array $payload): void
    {
        $succeeded = ($payload['status'] ?? null) === 'completed';
        $output    = $payload['output'] ?? $command->result ?? [];
        $error     = $payload['message'] ?? $command->error ?? 'The agent reported a failure with no message.';

        $container = $this->resolveDockerContainer($command);

        if (!$container) {
            Log::warning('[HandleVmAgentEventJob] docker command result for an unresolvable container', [
                'operation'  => $command->operation,
                'command_id' => $command->uuid,
                'params'     => $command->params,
            ]);
            return;
        }

        if (!$succeeded) {
            DockerContainersService::update($container->uuid, [
                'status'      => 'error',
                'agent_error' => $error,
            ]);
            return;
        }

        match ($command->operation) {
            'docker.create' => DockerContainersService::update($container->uuid, [
                'container_id'       => $output['id'] ?? $container->container_id,
                'status'             => $output['state'] ?? 'running',
                'agent_error'        => null,
                'last_agent_sync_at' => now(),
            ]),
            'docker.start', 'docker.restart' => DockerContainersService::update($container->uuid, [
                'status'             => 'running',
                'agent_error'        => null,
                'last_agent_sync_at' => now(),
            ]),
            'docker.stop' => DockerContainersService::update($container->uuid, [
                'status'             => 'stopped',
                'agent_error'        => null,
                'last_agent_sync_at' => now(),
            ]),
            'docker.remove' => DockerContainersService::update($container->uuid, [
                'status'             => 'removed',
                'agent_error'        => null,
                'last_agent_sync_at' => now(),
            ]),
            default => null,
        };
    }

    /**
     * docker.create is correlated via the 'client_ref' param (the
     * DockerContainers row's own uuid, set by Actions\DockerContainers\Create) -
     * the container has no container_id yet at dispatch time, so container_id
     * can't be used. Every other docker.* action already knows container_id,
     * so it's matched directly (scoped to this command's own agent_uuid, i.e.
     * this VM, since Docker's own IDs are effectively globally unique but
     * scoping costs nothing here).
     */
    private function resolveDockerContainer(AgentCommands $command): ?DockerContainers
    {
        $params = $command->params ?? [];

        if ($command->operation === 'docker.create') {
            $clientRef = $params['client_ref'] ?? null;

            return $clientRef ? DockerContainers::where('uuid', $clientRef)->first() : null;
        }

        $containerId = $params['container_id'] ?? null;

        if (!$containerId) {
            return null;
        }

        return DockerContainers::whereHas('virtualMachine', function ($query) use ($command) {
            $query->where('uuid', $command->agent_uuid);
        })->where('container_id', $containerId)->first();
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
