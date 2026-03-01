<?php

namespace NextDeveloper\IAAS\Jobs;

use Google\Service\Compute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Communication\Helpers\Communicate;
use NextDeveloper\IAAS\Actions\ComputeMembers\ScanVirtualMachines;
use NextDeveloper\IAAS\Actions\ComputeMembers\UpdateResources;
use NextDeveloper\IAAS\Actions\ComputeMembers\UpdateStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberTasks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackupsPerspective;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class DeleteVirtualMachineBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $vm;

    public function __construct(public VirtualMachineBackups $vmBackups) {
        $this->queue = 'iaas-misc';
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        $repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->vmBackups->iaas_repository_id)
            ->first();

        $explodedPath = explode(':', $this->vmBackups->path);

        try {
            $result = $repository->performSSHCommand('ls ' . $explodedPath[1]);

            logger()->debug($result['output']);

            //$this->vmBackups->delete();
        } catch (CannotConnectWithSshException $exception) {
            StateHelper::setState($repository, 'ssh_error', $exception->getMessage());
        }
    }
}
