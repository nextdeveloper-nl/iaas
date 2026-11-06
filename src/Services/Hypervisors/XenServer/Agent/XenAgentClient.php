<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * HTTPS client for the xenserver agent (POST https://<host>:8443/v1/command, bearer token).
 * Protocol reference: xenserver.agent/docs/operations.md.
 *
 * Contract:
 *  - call() returns the operation's `output`; throws XenAgentException on transport errors,
 *    status failed|rejected, output.success=false, or an unexpected status. There is no
 *    fallback to SSH.
 *  - callAsync() starts the operation with params.async=true and polls agent.job.status until
 *    the job finishes, returning the job's `result`.
 *  - leo.debug.ssh.dry_run is honoured: nothing is sent and ['dry_run' => true] is returned,
 *    same flag the SSH transport uses.
 *  - The bearer token and raw agent stderr/stdout are never written to logs.
 */
class XenAgentClient
{
    public function __construct(private readonly ComputeMembers $computeMember)
    {
    }

    /**
     * Runs one operation and returns its output (an array for read operations, the
     * {operation,target,success,message,data,...} block for state changers).
     *
     * @throws XenAgentException
     */
    public function call(string $operation, array $params = [], ?int $timeoutSeconds = null): array
    {
        if (config('leo.debug.ssh.dry_run')) {
            return ['dry_run' => true];
        }

        $this->assertOperationAllowed($operation);

        $payload = $this->send($operation, $params, $timeoutSeconds);
        $status  = $payload['status'] ?? null;

        if ($status === 'rejected' || $status === 'failed') {
            throw new XenAgentException(
                'Agent ' . $status . ' ' . $operation . ': ' . ($payload['message'] ?? 'no message'),
                $operation,
                $status,
                $payload['output']['detail'] ?? []
            );
        }

        if ($status !== 'completed' && $status !== 'accepted') {
            throw new XenAgentException(
                'Agent returned unexpected status "' . $status . '" for ' . $operation,
                $operation,
                $status
            );
        }

        $output = $payload['output'] ?? [];

        // status=completed only means the agent ran the command - state changers report the
        // real outcome in output.success. Read operations return plain JSON with no such key.
        if (is_array($output) && array_key_exists('success', $output) && $output['success'] === false) {
            throw new XenAgentException(
                'Agent operation ' . $operation . ' failed: ' . ($output['message'] ?? 'no message'),
                $operation,
                $status,
                $output['detail'] ?? []
            );
        }

        return is_array($output) ? $output : ['value' => $output];
    }

    /**
     * Starts a long operation (export, import, clone, copy, sr.scan) as an agent background job
     * and waits for it. Returns the job's result (the operation's normal output).
     *
     * @throws XenAgentException on failure, cancellation or when maxWait runs out
     */
    public function callAsync(string $operation, array $params = [], ?int $maxWaitSeconds = null): array
    {
        if (config('leo.debug.ssh.dry_run')) {
            return ['dry_run' => true];
        }

        $maxWait  = $maxWaitSeconds ?? (int) config('virtualization.agent.async.max_wait', 3600);
        $interval = max(1, (int) config('virtualization.agent.async.poll_interval', 3));

        $started = $this->call($operation, array_merge($params, ['async' => true]));
        $jobId   = $started['job_id'] ?? null;

        if (!$jobId) {
            // The agent ran it synchronously (some operations ignore async) - treat as done.
            return $started;
        }

        $deadline = time() + $maxWait;

        while (time() < $deadline) {
            sleep($interval);

            $job = $this->call('agent.job.status', ['job_id' => $jobId]);

            switch ($job['status'] ?? null) {
                case 'completed':
                    $result = $job['result'] ?? [];

                    // The job's result is the operation's own output, so it can still say success=false.
                    if (is_array($result) && ($result['success'] ?? true) === false) {
                        throw new XenAgentException(
                            'Agent job ' . $jobId . ' (' . $operation . ') failed: ' . ($result['message'] ?? 'no message'),
                            $operation,
                            'completed',
                            $result['detail'] ?? []
                        );
                    }

                    return is_array($result) ? $result : ['value' => $result];

                case 'failed':
                case 'cancelled':
                    throw new XenAgentException(
                        'Agent job ' . $jobId . ' (' . $operation . ') ended as ' . $job['status'] . ': ' .
                        ($job['message'] ?? $job['error'] ?? 'no message'),
                        $operation,
                        $job['status'],
                        $job['result']['detail'] ?? []
                    );
            }
        }

        // Stop the agent-side work so a half-finished export/copy does not keep running unattended.
        try {
            $this->call('agent.job.cancel', ['job_id' => $jobId]);
        } catch (XenAgentException $e) {
            Log::warning('[XenAgentClient@callAsync] Could not cancel timed out job ' . $jobId, $e->getSafeContext());
        }

        throw new XenAgentException(
            'Agent job ' . $jobId . ' (' . $operation . ') did not finish within ' . $maxWait . ' seconds.',
            $operation
        );
    }

    /**
     * Sends one envelope and returns the reply's `payload`. Keeps the same connection-problem
     * accounting as ComputeMemberXenService::performCommand, so a dead agent marks the host the
     * way a dead SSH connection does.
     */
    private function send(string $operation, array $params, ?int $timeoutSeconds): array
    {
        $endpoint = AgentEndpoint::forComputeMember($this->computeMember);

        $envelope = [
            'v'       => 1,
            'id'      => (string) Str::uuid(),
            'type'    => 'command',
            'payload' => [
                'operation' => $operation,
                'params'    => (object) $params,
            ],
        ];

        try {
            $response = Http::withToken($endpoint->token)
                ->withOptions($endpoint->httpOptions)
                ->acceptJson()
                ->asJson()
                ->connectTimeout((int) config('virtualization.agent.connect_timeout', 10))
                ->timeout($timeoutSeconds ?? (int) config('virtualization.agent.timeout', 60))
                ->post($endpoint->baseUrl, $envelope);
        } catch (ConnectionException $e) {
            $this->recordConnectionProblem($operation, $e->getMessage());

            throw new XenAgentException(
                'Cannot reach the agent of compute member ' . $this->computeMember->name . ': ' . $e->getMessage(),
                $operation,
                null,
                [],
                $e
            );
        }

        $body = $response->json();

        // 401/403 and other HTTP errors may carry no envelope at all.
        if (!is_array($body) || !isset($body['payload'])) {
            throw new XenAgentException(
                'Agent answered ' . $operation . ' with HTTP ' . $response->status() . ' and no result envelope.',
                $operation
            );
        }

        $this->recordConnectionOk();

        return $body['payload'];
    }

    /**
     * Fails loudly when the host's published capability list exists and does not contain the
     * operation. When the list was never fetched (empty) the agent itself decides and answers
     * status=rejected, so a fresh host is not blocked by missing local data.
     */
    private function assertOperationAllowed(string $operation): void
    {
        // agent.job.* and agent.allowed_operations are agent control calls, always usable.
        if (str_starts_with($operation, 'agent.')) {
            return;
        }

        $available = ($this->computeMember->available_operations ?? [])['agent'] ?? [];

        if (empty($available)) {
            return;
        }

        if (!in_array($operation, array_column($available, 'operation'), true)) {
            throw new XenAgentException(
                'Operation ' . $operation . ' is not in the allowed operations of compute member ' .
                $this->computeMember->name . '.',
                $operation,
                'rejected'
            );
        }
    }

    protected function recordConnectionOk(): void
    {
        StateHelper::setState(
            $this->computeMember,
            'connection_problem_count',
            0,
            StateHelper::STATE_SUCCESS,
            'The agent connection is OK'
        );

        if (!$this->computeMember->is_alive) {
            $this->computeMember->updateAsAdministrator(['is_alive' => true]);
        }
    }

    protected function recordConnectionProblem(string $operation, string $error): void
    {
        Log::error('[XenAgentClient@recordConnectionProblem] Agent unreachable for compute member ' .
            $this->computeMember->name . ' while running ' . $operation);

        $state = StateHelper::getState($this->computeMember, 'connection_problem_count');
        $count = ($state ? (int) $state->value : 0) + 1;

        Events::fire('connection-problem:NextDeveloper\IAAS\ComputeMembers', $this->computeMember);

        StateHelper::setState(
            $this->computeMember,
            'connection_problem_count',
            $count,
            StateHelper::STATE_ERROR,
            'The agent is unreachable while running ' . $operation . ': ' . $error
        );

        // Same threshold as the SSH path: after more than 5 failures the host is marked dead,
        // and ComputeMembersObserver reacts to the is_alive transition.
        if ($count > 5) {
            $this->computeMember->updateAsAdministrator([
                'is_alive'  => false,
                'has_error' => true,
            ]);
        }
    }
}
