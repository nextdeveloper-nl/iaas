<?php

namespace NextDeveloper\IAAS\Jobs\BackupJobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\BackupJobsService;
use NextDeveloper\IAAS\Services\BackupRetentionPoliciesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

class CreateDefaultBackupJobsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public VirtualMachines $vm)
    {
    }

    public function handle(): void
    {
        logger()->debug(__METHOD__ . '| Starting default backup jobs creation process for VM: ' . $this->vm->uuid);

        BackupJobsService::createDefaultVmBackupJob($this->vm);

        logger()->debug(__METHOD__ . '| Finished default backup jobs creation process for VM: ' . $this->vm->uuid);
    }
}
