<?php

namespace NextDeveloper\IAAS\Jobs\VirtualMachines;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\ComputeMemberAgentSyncService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Picks up a lazy VM import that runs as an agent job and resumes the Commit action when it is done.
 *
 * The SSH path starts `xe vm-import ... &` and the host calls our finalize-commit endpoint when it
 * finishes. The agent's callback needs an allow-listed https URL in the host config, so this job
 * polls agent.job.status instead. It does not wait inside the worker: while the import is still
 * running it puts itself back on the queue with a delay, so a long import never holds a worker.
 *
 * On success it stores the new VM's hypervisor_uuid (from the job result) and dispatches Commit,
 * which then runs its second stage. VirtualMachinesService::finalizeCommit() is not used because
 * it looks the VM up by name over SSH (xe vm-list).
 */
class FinalizeAgentImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * How many times the job looks again, and how long it waits between looks (seconds).
     * 15 s x 480 = two hours, longer than any import should take.
     */
    public const MAX_ATTEMPTS = 480;
    public const RETRY_DELAY  = 15;

    public function __construct(
        private readonly VirtualMachines $vm,
        private readonly string $jobId,
        private readonly int $attempt = 1
    ) {
        $this->queue = 'iaas';
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        $vm            = $this->vm->fresh();
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        try {
            $job = ComputeMemberAgentSyncService::client($computeMember)->call('agent.job.status', ['job_id' => $this->jobId]);
        } catch (XenAgentException $e) {
            //  The agent can be restarting. Look again later instead of giving up on one error.
            Log::warning('[FinalizeAgentImport@handle] Cannot read job ' . $this->jobId . ' of VM ' . $vm->uuid .
                ': ' . $e->getMessage());

            $this->again();

            return;
        }

        switch ($job['status'] ?? null) {
            case 'completed':
                $uuid = $job['result']['data']['uuid'] ?? null;

                if (($job['result']['success'] ?? true) === false || !$uuid) {
                    $this->fail_($vm, 'The import job finished without a VM: ' . ($job['result']['message'] ?? 'no message'));

                    return;
                }

                UserHelper::runAsAdmin(fn() => $vm->update(['hypervisor_uuid' => $uuid]));

                //  Same user context finalizeCommit() sets before it dispatches Commit.
                UserHelper::setUserById($vm->iam_user_id);
                UserHelper::setCurrentAccountById($vm->iam_account_id);

                dispatch(new Commit($vm->fresh()));

                return;

            case 'failed':
            case 'cancelled':
                $this->fail_($vm, 'The import job ' . $job['status'] . ': ' . ($job['message'] ?? $job['error'] ?? 'no message'));

                return;
        }

        $this->again();
    }

    private function again(): void
    {
        if ($this->attempt >= self::MAX_ATTEMPTS) {
            $this->fail_($this->vm->fresh(), 'The import job did not finish in time.');

            return;
        }

        self::dispatch($this->vm, $this->jobId, $this->attempt + 1)->delay(now()->addSeconds(self::RETRY_DELAY));
    }

    private function fail_(VirtualMachines $vm, string $reason): void
    {
        Log::error('[FinalizeAgentImport@handle] VM ' . $vm->uuid . ': ' . $reason);

        CommentsService::createSystemComment('Deploying this virtual machine failed: ' . $reason, $vm);

        UserHelper::runAsAdmin(fn() => $vm->update(['status' => 'deploy-failed']));
    }
}
