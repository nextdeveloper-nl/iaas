<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

/**
 * Raised by XenAgentClient for every failure of an agent call (transport error, rejected or
 * failed envelope, output.success=false, job timeout). The agent driver deliberately has no
 * silent SSH fallback, so callers see exactly why a call failed.
 *
 * `detail` carries the agent's own {command, exit_code, error_code, stderr, stdout} block when
 * it sent one. It can echo secrets, so it is never logged in full - use getSafeContext().
 */
class XenAgentException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?string $operation = null,
        private readonly ?string $agentStatus = null,
        private readonly array $detail = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * completed|failed|rejected|accepted as sent by the agent, or null for transport errors.
     */
    public function getAgentStatus(): ?string
    {
        return $this->agentStatus;
    }

    public function getDetail(): array
    {
        return $this->detail;
    }

    /**
     * Log-safe view: operation, status and exit/error codes only. stderr, stdout and the raw
     * command are left out because they can contain xenstore-data values or passwords.
     */
    public function getSafeContext(): array
    {
        return [
            'operation'  => $this->operation,
            'status'     => $this->agentStatus,
            'exit_code'  => $this->detail['exit_code'] ?? null,
            'error_code' => $this->detail['error_code'] ?? null,
        ];
    }
}
