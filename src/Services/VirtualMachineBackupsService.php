<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Actions\BackupJobs\FinishBackupJob;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Jobs\DeleteVirtualMachineBackupJob;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachineBackupsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for VirtualMachineBackups
 *
 * Class VirtualMachineBackupsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachineBackupsService extends AbstractVirtualMachineBackupsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getRepositoryImage(VirtualMachineBackups $backup) : ?RepositoryImages
    {
        return RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $backup->iaas_repository_image_id)
            ->first();
    }

    public static function getRepository(VirtualMachineBackups $backup)
    {
        $backupJob = BackupJobs::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $backup->iaas_backup_job_id)
            ->first();

        $repo = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $backupJob->iaas_repository_id)
            ->first();

        return $repo;
    }

    public static function finalizeBackup($uuid)
    {
        $vmBackup = VirtualMachineBackups::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $uuid)
            ->first();

        UserHelper::setUserById($vmBackup->iam_user_id);
        UserHelper::setCurrentAccountById($vmBackup->iam_account_id);

        $backupJob = BackupJobs::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vmBackup->iaas_backup_job_id)
            ->first();

        (new FinishBackupJob($backupJob, [
            'iaas_virtual_machine_backup_id'   =>  $vmBackup->id
        ]))->handle();
    }

    public static function delete($id)
    {
        $backup = null;

        if(Str::isUuid($id))
            $backup = VirtualMachineBackups::where('uuid', $id)->first();
        else
            $backup = VirtualMachineBackups::where('id', $id)->first();

        dispatch(new DeleteVirtualMachineBackupJob($backup));

        return null;
    }
}
