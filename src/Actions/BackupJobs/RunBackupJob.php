<?php

namespace NextDeveloper\IAAS\Actions\BackupJobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualMachines\Backup;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\BackupJobsService;
use NextDeveloper\IAAS\Services\Backups\BackupService;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

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

        $this->setProgress(100, 'Finished for backup job: ' . $this->model->uuid);
    }

    private function backupVirtualMachine($vm)
    {
        $vmBackup = $this->getStateData(
            key: 'vm_backup',
            default: BackupService::getPendingBackup($vm, $this->model)
        );

        if(is_array($vmBackup))
            $vmBackup = VirtualMachineBackups::withoutGlobalScopes()->find($vmBackup['id']);

        if(!$vmBackup) {
            $vmBackup = BackupService::createPendingBackup($vm, $this->model);
            BackupService::setBackupState($vmBackup, 'initiated');

            $this->setStateData(
                'vm_backup',
                $vmBackup
            );
        }
        else {
            BackupService::setBackupState($vmBackup, 'restarting');
        }

        $backupStarts = Carbon::now();

        $this->setStateData('backup_starts', $backupStarts);

        if($vm->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($vm->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        //  Converting back to latest state just incase we need to rerun this job.
        $snapshot = $this->getStateData('snapshot', null);
        $clonedVm = $this->getStateData('cloned_vm', null);
        $uuid = $this->getStateData('snapshot_uuid', null);
        $backupRepo = $this->getStateData('backup_repo', null);
        $exportPath = $this->getStateData('export_path', null);
        $backupFilename = $this->getStateData('backup_filename', null);

        if(is_array($snapshot)) $snapshot = (new VirtualMachines())->fill($snapshot);
        if(is_array($clonedVm)) $clonedVm = (new VirtualMachines())->fill($clonedVm);
        if(is_array($backupRepo)) $backupRepo = (new Repositories())->fill($backupRepo);

        if($this->shouldRunCheckpoint(10)) {
            $snapshot = VirtualMachinesXenService::takeSnapshot($vm);

            if($snapshot['error']) {
                //  There is an error
                dd($snapshot);
            }

            $uuid = $snapshot['output'];

            Log::info('[' . __METHOD__ . '] Taken the snapshot. The uuid of snapshot: ' . $uuid);

            $this->setProgress(10, 'Snapshot of the virtual machine is taken.');

            $this->setStateData('snapshot_uuid', $uuid);
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

            $this->setStateData('snapshot', $snapshot);
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
            $clonedVmUuid = $clonedVm['output'];

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

            $this->setStateData('cloned_vm', $clonedVm);
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

            $backupRepo = $this->getStateData(
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
            BackupService::setBackupState($vmBackup, 'running');

            $backupFilename = $this->getStateData(
                'backup_filename',
                $clonedVm->uuid . '.' . (new Carbon($clonedVm->created_at))->timestamp . '.pvm'
            );

            $exportPath = $this->getStateData(
                'export_path',
                $backupRepo->local_ip_addr . ':' . $backupRepo->vm_path . '/' . $backupFilename
            );

            //  This may take up to few hours.
            //  We need to make sure that the job does not time out.
            $backupResult = VirtualMachinesXenService::exportToRepository(
                vm: $clonedVm,
                repositories: $backupRepo,
                exportName: $backupFilename
            );

            $this->setProgress(80, 'Exported VM to the default backup repository.');
        }

        if($this->shouldRunCheckpoint(90)) {
            //  We need to make sure that the backup state is set to backed-up.
            //  Because if we have a failure in the next steps, we still have a valid
            BackupService::setBackupState($vmBackup, 'backed-up');

            $backupEnds = Carbon::now();
            $backupDiff = $backupEnds->diffInSeconds($backupStarts);

            $this->setStateData('backup_ends', $backupEnds);
            $this->setStateData('backup_diff', $backupDiff);

            $vmBackup->update([
                'path'  =>  $exportPath,
                'filename'  =>  $backupFilename,
                'status'    =>  'backed-up',
                'backup-type'   =>  'full-backup',
                'iaas_repository_id'    =>  $backupRepo->id
            ]);

            $this->setStateData('vm_backup', $vmBackup->fresh());

            $repoImage = RepositoryImagesService::create([
                'iaas_repository_id'    =>  $backupRepo->id,
                'name'                  =>  'Backup of ' . $vm->name,
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

            $this->setStateData('repo_image', $repoImage);

            //  Updating the vm backup to understand where is the image.
            //  In the future we will be removing this table (most probably)
            $vmBackup->updateQuietly([
                'iaas_repository_image_id'  =>  $repoImage->id
            ]);

            $this->setStateData('vm_backup', $vmBackup->fresh());

            RepositoryImagesService::updateRepoSize($repoImage);

            $this->setProgress(90, 'VM exported. It took: ' . $backupDiff . ' seconds.');
        }

        if($this->shouldRunCheckpoint(95)) {
            Events::fire('backed-up:NextDeveloper\IAAS\VirtualMachines', $vm);

            VirtualMachinesXenService::destroyVm($clonedVm);

            $clonedVm->delete();

            $this->setProgress(95, 'Removed VM that was cloned.');
        }

        $this->setFinished('Virtual machine backup finished');
    }
}
