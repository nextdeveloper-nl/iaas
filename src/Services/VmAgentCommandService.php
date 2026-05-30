<?php

namespace NextDeveloper\IAAS\Services;

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
        $available = ($vm->available_operations ?? [])['agent'] ?? [];

        if (!in_array($operation, $available, true)) {
            throw new \InvalidArgumentException(
                "Operation '{$operation}' is not available for this VM agent. Available: " . implode(', ', $available)
            );
        }

        return AgentCommandsService::dispatch('vm', $vm->uuid, $operation, $params, $timeoutS);
    }

    public static function getAvailableOperations(VirtualMachines $vm): array
    {
        return $vm->available_operations ?? [];
    }
}
