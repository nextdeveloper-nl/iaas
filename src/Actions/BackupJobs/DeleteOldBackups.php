<?php

namespace NextDeveloper\IAAS\Actions\BackupJobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Communication\Helpers\Communicate;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\Repositories\SynchronizeMachineImages;
use NextDeveloper\IAAS\Actions\VirtualMachines\Delete;
use NextDeveloper\IAAS\Actions\VirtualMachines\Sync;
use NextDeveloper\IAAS\Console\Commands\SyncRepositoryMachineImages;
use NextDeveloper\IAAS\Console\Commands\SyncVirtualMachine;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Helpers\VmBackupDataHelper;
use NextDeveloper\IAAS\Jobs\DeleteVirtualMachineBackupJob;
use NextDeveloper\IAAS\Services\BackupJobsService;
use NextDeveloper\IAAS\Services\Backups\BackupService;
use NextDeveloper\IAAS\Services\Repositories\SyncRepositoryService;
use NextDeveloper\IAAS\Services\RepositoriesService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachineBackupsService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class executes the backup job
 */
class DeleteOldBackups extends AbstractAction
{
    /**
     * Events related to enabling the service.
     */
    public const  EVENTS = [
        'cleanup-started:NextDeveloper\IAAS\BackupJobs',
        'cleaning:NextDeveloper\IAAS\BackupJobs',
        'cleaned-up:NextDeveloper\IAAS\BackupJobs',
    ];

    public const CHECKPOINTS = [
        '0'     =>  'Starting to delete old backups',
        '25'    =>  'Getting the old backups',
        '50'    =>  'Deleting the old backup',
        '100'    =>  'Finishing the job',
    ];

    public const PARAMS = [];

    /**
     * Finalize backup constructor
     *
     * @param BackupJobs $backupJob
     * @param $params
     * @param $previousAction
     * @throws NotAllowedException
     */
    public function __construct(BackupJobs $backupJob, $params = null, $previousAction = null)
    {
        $this->model = $backupJob;

        parent::__construct($params, $previousAction);
    }

    /**
     * Handles the enabling of the service.
     *
     * This method updates the account to set the service as enabled and logs the progress.
     */
    public function handle(): void
    {
        $this->setProgress(0, 'Starting for backup job: ' . $this->model->uuid);

        UserHelper::setAdminAsCurrentUser();

        $this->setProgress(25, 'Getting the old backups');

        $oldBackups = BackupService::getBackupsForDeletion($this->model);

        $this->setProgress('50', 'Deleting old backups');

        foreach ($oldBackups as $backup) {
            $b = VirtualMachineBackups::where('id', $backup->id)->first();
            dispatch(new DeleteVirtualMachineBackupJob($b));
        }
    }
}
