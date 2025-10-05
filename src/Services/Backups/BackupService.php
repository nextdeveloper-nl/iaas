<?php

namespace NextDeveloper\IAAS\Services\Backups;

use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class BackupService
{
    public static function getRunningBackups(ComputeMembers $members)
    {
        $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_compute_member_id', $members->id)
            ->pluck('id');

        $backups = VirtualMachineBackups::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('status', 'running')
            ->whereIn('iaas_virtual_machine_id', $vms)
            ->get();

        return $backups;
    }

    public static function isBackupRunning(ComputeMembers $members)
    {
        return count(self::getRunningBackups($members)) > 0;
    }

    public static function getPendingBackup(VirtualMachines $vm, BackupJobs $job) : ?VirtualMachineBackups
    {
        $backup = VirtualMachineBackups::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('status', '!=', 'backed-up')
            ->where('iaas_backup_job_id', $job->id)
            ->first();

        return $backup;
    }

    public static function createPendingBackup(VirtualMachines $vm, BackupJobs $job) : VirtualMachineBackups
    {
        return VirtualMachineBackups::create([
            'name'  =>  'Backup of ' . $vm->name,
            'username'  =>  $vm->username,
            'password'  =>  $vm->password,
            'size'      =>  0,
            'ram'       =>  $vm->ram,
            'cpu'       =>  $vm->cpu,
            'hash'      =>  0,
            'status'    =>  'pending',
            'backup-type'   =>  'full-backup',
            'iaas_virtual_machine_id'   =>  $vm->id,
            'iaas_backup_job_id' => $job->id,
            'iam_account_id'    =>  UserHelper::currentAccount()->id,
            'iam_user_id'   =>  UserHelper::currentUser()->id,
        ]);
    }

    public static function setBackupState(VirtualMachineBackups $backup, $state) : VirtualMachineBackups
    {
        $backup->update([
            'status'    =>  $state
        ]);

        return $backup->fresh();
    }
}
