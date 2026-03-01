<?php

namespace NextDeveloper\IAAS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
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

        $repoImage = RepositoryImages::where('id', $this->vmBackups->iaas_repository_image_id)->first();

        if(!$repoImage) {
            logger()->error('Trying to delete virtual machine backup without a repository image');
            return;
        }

        $repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $repoImage->iaas_repository_id)
            ->first();

        $explodedPath = explode(':', $this->vmBackups->path);

        try {
            $result = $repository->performSSHCommand('rm ' . $explodedPath[1]);

            logger()->debug('[DeleteVirtualMachineBackupJob] Output : ' . $result['output']);

            $this->vmBackups->delete();
        } catch (CannotConnectWithSshException $exception) {
            StateHelper::setState($repository, 'ssh_error', $exception->getMessage());
        } catch (\Exception $exception) {
            StateHelper::setState($this->vmBackups, 'delete_exception', $exception->getMessage());
        }
    }
}
