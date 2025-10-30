<?php

namespace NextDeveloper\IAAS\Actions\BackupJobs;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Services\RepositoriesService;
use NextDeveloper\IAAS\Services\VirtualMachineBackupsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class executes the backup job
 */
class FinishBackupJob extends AbstractAction
{
    /**
     * Events related to enabling the service.
     */
    public const  EVENTS = [
        'backup-started:NextDeveloper\IAAS\BackupJobs',
        'backing-up:NextDeveloper\IAAS\BackupJobs',
        'backup-completed:NextDeveloper\IAAS\BackupJobs',
    ];

    public const CHECKPOINTS = [
        '0'     =>  'Starting the backup process',
        '25'    =>  'Gathering information about backup',
        '50'    =>  'Synchronizing the backup file.',
        '80'    =>  'Hashing the backup file',
        '100'    =>  'Finalizing the backup',
    ];

    public const PARAMS = [
        'iaas_virtual_machine_backup_id' =>  'required|exists:iaas_virtual_machine_backups,id',
    ];


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

        /**
         * Here we should do;
         *
         * 1) Check if the xe vm-export job is still running in the compute member.
         * 2) Check if the file is in the repository
         * 3) Hash the file
         * 4) Save it
         * 5) Send email if the backup is finished
         */

        $vmBackup = VirtualMachineBackups::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->params['iaas_virtual_machine_backup_id'])
            ->first();

        if(!$vmBackup)
            $this->setFinishedWithError('Cannot find Virtual Machine Backup object');

        $repository = VirtualMachineBackupsService::getRepository($vmBackup);

        $checkRepository = RepositoriesService::checkBackup($repository, $vmBackup->filename);

        Events::fire('backup-completed:NextDeveloper\IAAS\BackupJobs', $vmBackup);
    }
}
