<?php

namespace NextDeveloper\IAAS\Actions\BackupJobs;

use App\Services\IAAS\VirtualMachineServices;
use Carbon\Carbon;
use Google\Service\GKEHub\State;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualMachines\Backup;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Helpers\VmBackupDataHelper;
use NextDeveloper\IAAS\Services\BackupJobsService;
use NextDeveloper\IAAS\Services\Backups\BackupService;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\Repositories\SyncRepositoryService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class executes the backup job
 */
class InitiateMultilevelBackupJob extends AbstractAction
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
        '10'    =>  'Taking the snapshot of the virtual machine',
        '20'    =>  'Snapshot is taken, creating the snapshot object.',
        '30'    =>  'Fixing the name of the snapshot.',
        '40'    =>  'Converting Snapshot to VM.',
        '50'    =>  'Cloning the VM.',
        '55'    =>  'Deleting the snapshot.',
        '60'    =>  'Fixing the cloned vm name.',
        '65'    =>  'Mounting default backup repository.',
        '75'    =>  'Removing all the VIFs of cloned VM.',
        '80'    =>  'Exporting to the default backup repository.',
        '90'    =>  'VM exported, removing the cloned VM.',
        '95'    =>  'Removed VM that was cloned.',
        '100'   =>  'Virtual machine backup finished'
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

        $this->resumeFromAction();

        switch ($this->model->object_type) {
            case VirtualMachines::class:
                $this->backupVirtualMachine($backupObject);
                break;
        }

        $this->setFinished('Finished for backup job: ' . $this->model->uuid);
    }

    private function backupVirtualMachine($vm)
    {
        $vmBackup = BackupService::getPendingBackup($vm, $this->model);
        BackupService::setBackupState($vmBackup, 'restarting');

        $backupStarts = Carbon::now();

        $vmBackupHelper = new VmBackupDataHelper($vmBackup);

        $vmBackupHelper->setData('backup_starts', $backupStarts);

        if($vm->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($vm->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        $snapshot = $vmBackupHelper->setData('snapshot', null);
        //  Converting back to latest state just incase we need to rerun this job.
        $snapshot = $vmBackupHelper->setData('snapshot', null);
        $clonedVm = $vmBackupHelper->setData('cloned_vm', null);
        $uuid = $vmBackupHelper->setData('snapshot_uuid', null);
        $backupRepo = $vmBackupHelper->setData('backup_repo', null);
        $exportPath = $vmBackupHelper->setData('export_path', null);
        $backupFilename = $vmBackupHelper->setData('backup_filename', null);
        $computeMember = $vmBackupHelper->setData(
            key: 'compute_member',
            default: VirtualMachinesService::getComputeMember($vm)
        );

        if(is_array($snapshot)) $snapshot = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $snapshot['uuid'])->first();
        if(is_array($clonedVm)) $clonedVm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $clonedVm['uuid'])->first();
        if(is_array($backupRepo)) $backupRepo = Repositories::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $backupRepo['uuid'])->first();
        if(is_array($computeMember)) $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $computeMember['uuid'])->first();

        if($this->shouldRunCheckpoint(10)) {
            $snapshot = VirtualMachinesXenService::takeSnapshot($vm);

            if($snapshot['error']) {
                //  There is an error
                dd($snapshot);
            }

            $uuid = $snapshot['output'];

            Log::info('[' . __METHOD__ . '] Taken the snapshot. The uuid of snapshot: ' . $uuid);

            $this->setProgress(10, 'Snapshot of the virtual machine is taken.');

            $vmBackupHelper->setData('snapshot_uuid', $uuid);
        }

        if($this->shouldRunCheckpoint(20)) {
            $snapshot = VirtualMachinesService::create([
                'name'  =>  'Snapshot of ' . $vm->name,
                'hypervisor_uuid'   =>  $uuid,
                'is_snapshot'   =>  true,
                'is_draft'  =>  false,
                'os'    =>  $vm->os,
                'distro'    =>  $vm->distro,
                'version'   =>  $vm->version,
                'status'    =>  'halted',
                'cpu'   =>  $vm->cpu,
                'ram'   =>  $vm->ram,
                'auto_backup_interval'  =>  'none',
                'auto_backup_time'  =>  'none',
                'iaas_compute_pool_id'  =>  $vm->iaas_compute_pool_id,
                'iaas_compute_member_id'    =>  $vm->iaas_compute_member_id,
                'iaas_cloud_node_id'  =>  $vm->iaas_cloud_node_id
            ]);

            $this->setProgress(20, 'Snapshot is taken, creating the snapshot object.');

            $vmBackupHelper->setData('snapshot', $snapshot);
        }

        if($this->shouldRunCheckpoint(30)) {
            VirtualMachinesXenService::fixName($snapshot);

            $this->setProgress(30, 'Fixed the name of the snapshot.');
        }

        if($this->shouldRunCheckpoint(40)) {
            //  Converting snapshot to VM does not require an update in VM details.
            $convertResult = VirtualMachinesXenService::convertSnapshotToVm($snapshot);

            $this->setProgress(40, 'Converting Snapshot to VM.');
        }

        if($this->shouldRunCheckpoint(50)) {
            $clonedVmUuid = VirtualMachinesXenService::cloneVm($snapshot);
            $clonedVmUuid = $clonedVmUuid['output'];

            Log::info('[' . __METHOD__ . '] VM is cloned, the new uuid is: ' . $clonedVmUuid);

            $clonedVm = VirtualMachinesService::create([
                'name'  =>  'Clone of ' . $vm->name,
                'hypervisor_uuid'   =>  $clonedVmUuid,
                'is_snapshot'   =>  true,
                'is_draft'  =>  false,
                'os'    =>  $vm->os,
                'distro'    =>  $vm->distro,
                'version'   =>  $vm->version,
                'status'    =>  'halted',
                'cpu'   =>  $vm->cpu,
                'ram'   =>  $vm->ram,
                'auto_backup_interval'  =>  'none',
                'auto_backup_time'  =>  'none',
                'iaas_compute_pool_id'  =>  $vm->iaas_compute_pool_id,
                'iaas_compute_member_id'    =>  $vm->iaas_compute_member_id,
                'iaas_cloud_node_id'  =>  $vm->iaas_cloud_node_id
            ]);

            $this->setProgress(50, 'VM is cloned.');

            $vmBackupHelper->setData('cloned_vm', $clonedVm);
        }

        if($this->shouldRunCheckpoint(55)) {
            //  Now we can delete the snapshot.
            $destroyResult = VirtualMachinesXenService::destroyVm($snapshot);
            $snapshot->delete();

            $this->setProgress(55, 'Snapshot is deleted.');
        }

        if($this->shouldRunCheckpoint(60)) {
            VirtualMachinesXenService::fixName($clonedVm);

            $this->setProgress(60, 'Fixed the cloned vm name.');
        }

        if($this->shouldRunCheckpoint(65)) {
            $computeMember = VirtualMachinesService::getComputeMember($clonedVm);

            $backupRepo = $vmBackupHelper->setData(
                key: 'backup_repo',
                default: ComputeMembersService::getDefaultBackupRepository($computeMember)
            );

            ComputeMemberXenService::mountRepository($computeMember, $backupRepo);

            $this->setProgress(65, 'Mounted default backup repository.');
        }

        if($this->shouldRunCheckpoint(75)) {
            $vifs = VirtualMachinesXenService::getVifs($clonedVm);

            foreach ($vifs as $vif) {
                VirtualMachinesXenService::destroyVif($clonedVm, $vif['uuid']);
            }

            $this->setProgress(75, 'Removed all the VIFs of cloned VM. Starting to export it.');
        }

        if($this->shouldRunCheckpoint(80)) {
            $isBackupRunning = VirtualMachinesXenService::isBackupRunning(
                computeMember: $computeMember,
                vmName: $clonedVm->name,
            );

            Log::debug('[RunBackupJob] The backup state fo the vmBackup: ' . $vmBackup->status);
            Log::debug('[RunBackupJob] Is backup running in background: ' . $isBackupRunning);

            //  If the backup state is running and isBackupRunning is false. Maybe the backup is already finished ?
            //  We should check this.
            if(BackupService::getBackupState($vmBackup) == 'running' && !$isBackupRunning) {
                //
            } else {
                BackupService::setBackupState($vmBackup, 'running');

                $backupFilename = $vmBackupHelper->setData(
                    'backup_filename',
                    $clonedVm->uuid . '.' . (new Carbon($clonedVm->created_at))->timestamp . '.pvm'
                );

                $exportPath = $vmBackupHelper->setData(
                    'export_path',
                    $backupRepo->local_ip_addr . ':' . $backupRepo->vm_path . '/' . $backupFilename
                );

                Log::debug('[RunBackupJob] Is backup running on step 2: ' . $isBackupRunning);

                if(!$isBackupRunning) {
                    $this->setProgress(76, 'Backup is not running therefor I am starting ' .
                        'the backup process');
                    //  This may take up to few hours.
                    //  We need to make sure that the job does not time out.
                    $backupResult = VirtualMachinesXenService::exportToRepositoryInBackground(
                        vm: $clonedVm,
                        repositories: $backupRepo,
                        exportName: $backupFilename
                    );
                }
            }

            $this->setProgress(80, 'Background backup job to repository started.');

            $vmBackup->update([
                'path'  =>  $exportPath,
                'filename'  =>  $backupFilename,
                'status'    =>  'backed-up',
                'backup-type'   =>  'full-backup',
                'iaas_repository_id'    =>  $backupRepo->id
            ]);

            $repoImage = RepositoryImagesService::create([
                'iaas_repository_id'    =>  $backupRepo->id,
                'name'                  =>  $vm->name,
                'filename'              =>  $backupFilename,
                'path'                  =>  $exportPath,
                'is_iso'                =>  false,
                'is_public'             =>  false,
                'ram'                   =>  $vm->ram,
                'cpu'                   =>  $vm->cpu,
                'default_username'  =>  $vm->username,
                'default_password'  =>  VirtualMachinesService::getRawPasswordById($vm->id),
                'is_virtual_machine_image'     =>  true,
                'os'        =>  $vm->os,
                'distro'    =>  $vm->distro,
                'version'   =>  $vm->version,
                'iaas_virtual_machine_id'   =>  $vm->id,
                'iam_account_id'        =>  $vm->iam_account_id,
                'iam_user_id'           =>  $vm->iam_user_id
            ]);

            $vmBackupHelper->setData('repo_image', $repoImage);

            //  Updating the vm backup to understand where is the image.
            //  In the future we will be removing this table (most probably)
            $vmBackup->updateQuietly([
                'iaas_repository_image_id'  =>  $repoImage->id
            ]);

            $this->setProgress(90, 'Saved all necessary data and moving with the background process.');
        }
    }
}
