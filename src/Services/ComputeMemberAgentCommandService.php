<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * Dispatches commands to compute member (XenServer host) agents via NATS.
 *
 * Valid operations are read from $computeMember->available_operations - no
 * operations are hardcoded here. The agent is responsible for keeping
 * available_operations current (see HandleComputeAgentEventJob).
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
        return $computeMember->sendAgentCommand($operation, $params, $timeoutS);
    }

    public static function getAvailableOperations(ComputeMembers $computeMember): array
    {
        return $computeMember->available_operations ?? [];
    }
}
