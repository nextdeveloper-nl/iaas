<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\Events\Services\AgentCommandsService;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * Dispatches commands to compute member (XenServer host) agents via NATS.
 *
 * Valid operations are read from $computeMember->available_operations - no
 * operations are hardcoded here. The agent is responsible for keeping
 * available_operations current (see ListenComputeAgentEvents).
 */
class ComputeMemberAgentCommandService
{
    /**
     * Send a command to the compute member's agent and return the command UUID
     * for tracking.
     *
     * @throws \InvalidArgumentException if $operation is not in available_operations
     */
    public static function dispatch(
        ComputeMembers $computeMember,
        string         $operation,
        array          $params = [],
        int            $timeoutS = 300
    ): string {
        $available = ($computeMember->available_operations ?? [])['agent'] ?? [];

        if (!in_array($operation, $available, true)) {
            throw new \InvalidArgumentException(
                "Operation '{$operation}' is not available for this compute member agent. Available: " . implode(', ', $available)
            );
        }

        return AgentCommandsService::dispatch('compute', $computeMember->uuid, $operation, $params, $timeoutS);
    }

    public static function getAvailableOperations(ComputeMembers $computeMember): array
    {
        return $computeMember->available_operations ?? [];
    }
}
