<?php

namespace NextDeveloper\IAAS\Actions\BackupJobs;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\IAAS\Actions\VirtualMachines\Backup;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * This class executes the backup job
 */
class RunBackupJob extends AbstractAction
{
    /**
     * Events related to enabling the service.
     */
    public const  EVENTS = [
        'backup-started:NextDeveloper\IAAS\BackupJobs',
        'backing-up:NextDeveloper\IAAS\BackupJobs',
        'backup-completed:NextDeveloper\IAAS\BackupJobs',
    ];

    /**
     * EnableService constructor.
     *
     * @param Accounts $accounts The accounts model instance.
     * @throws NotAllowedException If the action is not allowed.
     */
    public function __construct(BackupJobs $backupJob)
    {
        $this->model = $backupJob;
        parent::__construct();
    }

    /**
     * Handles the enabling of the service.
     *
     * This method updates the account to set the service as enabled and logs the progress.
     */
    public function handle(): void
    {
        $this->setProgress(0, 'Starting for backup job: ' . $this->model->uuid);

        $backupObject = app($this->model->object_type)::where('id', $this->model->object_id)->first();

        switch ($this->model->object_type) {
            case VirtualMachines::class:
                (new Backup($backupObject, [
                    'iaas_backup_job_id' => $this->model->id
                ]))->handle();
                break;
        }

        $this->setProgress(100, 'Finished for backup job: ' . $this->model->uuid);
    }
}
