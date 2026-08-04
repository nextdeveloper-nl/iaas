<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\Events\Database\Filters\AgentCommandsQueryFilter;
use NextDeveloper\Events\Services\AgentCommandsService;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Dispatches commands to VM agents via NATS.
 *
 * Valid operations are read from $vm->available_operations — no operations are
 * hardcoded here. The agent is responsible for keeping available_operations current.
 */
class VmAgentCommandService
{
    /**
     * Send a command to the VM agent and return the command UUID for tracking.
     *
     * @throws \InvalidArgumentException if $operation is not in available_operations
     */
    public static function dispatch(
        VirtualMachines $vm,
        string          $operation,
        array           $params = [],
        int             $timeoutS = 300
    ): string {
        return $vm->sendAgentCommand($operation, $params, $timeoutS);
    }

    public static function getAvailableOperations(VirtualMachines $vm): array
    {
        return $vm->available_operations ?? [];
    }

    /**
     * List previously dispatched agent commands for this VM (and their status/result),
     * scoping the shared event_agent_commands table down to this VM's own commands.
     * Any other filter (status, operation, date ranges, ...) from $params still applies
     * on top - only agent_type/agent_uuid are forced.
     */
    public static function getCommands(VirtualMachines $vm, AgentCommandsQueryFilter $filter, array $params = [])
    {
        $filter->updateValue('agentType', 'vm');
        $filter->updateValue('agentUuid', $vm->uuid);

        return AgentCommandsService::get($filter, $params);
    }
}
