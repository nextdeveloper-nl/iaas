<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\BackupJobsService;
use NextDeveloper\IAAS\Services\Backups\BackupService;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action converts the virtual machine into a template
 */
class Backup extends AbstractAction
{
    public const EVENTS = [
        'backing-up:NextDeveloper\IAAS\VirtualMachines',
        'backed-up:NextDeveloper\IAAS\VirtualMachines',
        'backup-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public const PARAMS = [
        'iaas_backup_job_id'  =>  'required|exists:iaas_backup_jobs,id',
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Backup virtual machine action started.');

        $backupJob = null;

        if(array_key_exists('iaas_backup_job_id', $this->params)) {
            $backupJob = BackupJobs::where('id', $this->params['iaas_backup_job_id'])->first();

            if(!$backupJob) {
                $backupJob = BackupJobsService::createDefaultVmBackupJob($this->model);
            }
        }

        $vmBackup = BackupService::getPendingBackup($this->model, $backupJob);

        if(!$vmBackup) {
            $vmBackup = BackupService::createPendingBackup($this->model, $backupJob);
            BackupService::setBackupState($vmBackup, 'initiated');
        }
        else {
            BackupService::setBackupState($vmBackup, 'restarting');
        }

        $backupStarts = Carbon::now();

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        $this->setProgress(10, 'Taking the snapshot of the virtual machine');

        $snapshot = VirtualMachinesXenService::takeSnapshot($this->model);

        if($snapshot['error']) {
            //  There is an error
            dd($snapshot);
        }

        $uuid = $snapshot['output'];

        Log::info('[' . __METHOD__ . '] Taken the snapshot. The uuid of snapshot: ' . $uuid);

        $this->setProgress(20, 'Snapshot is taken, creating the snapshot object.');

        $snapshot = VirtualMachinesService::create([
            'name'  =>  'Snapshot of ' . $this->model->name,
            'hypervisor_uuid'   =>  $uuid,
            'is_snapshot'   =>  true,
            'is_draft'  =>  false,
            'os'    =>  $this->model->os,
            'distro'    =>  $this->model->distro,
            'version'   =>  $this->model->version,
            'status'    =>  'halted',
            'cpu'   =>  $this->model->cpu,
            'ram'   =>  $this->model->ram,
            'auto_backup_interval'  =>  'none',
            'auto_backup_time'  =>  'none',
            'iaas_compute_pool_id'  =>  $this->model->iaas_compute_pool_id,
            'iaas_compute_member_id'    =>  $this->model->iaas_compute_member_id,
            'iaas_cloud_node_id'  =>  $this->model->iaas_cloud_node_id
        ]);

        $this->setProgress(30, 'Fixing the name of the snapshot.');

        VirtualMachinesXenService::fixName($snapshot);

        $this->setProgress(40, 'Converting Snapshot to VM.');

        $convertResult = VirtualMachinesXenService::convertSnapshotToVm($snapshot);

        $this->setProgress(50, 'Cloning the VM.');

        $clonedVm = VirtualMachinesXenService::cloneVm($snapshot);
        $clonedVm = $clonedVm['output'];

        $this->setProgress(55, 'Deleting the snapshot.');

        //  Now we can delete the snapshot.
        $destroyResult = VirtualMachinesXenService::destroyVm($snapshot);
        $snapshot->delete();

        Log::info('[' . __METHOD__ . '] VM is cloned, the new uuid is: ' . $clonedVm);

        $clonedVm = VirtualMachinesService::create([
            'name'  =>  'Clone of ' . $this->model->name,
            'hypervisor_uuid'   =>  $clonedVm,
            'is_snapshot'   =>  true,
            'is_draft'  =>  false,
            'os'    =>  $this->model->os,
            'distro'    =>  $this->model->distro,
            'version'   =>  $this->model->version,
            'status'    =>  'halted',
            'cpu'   =>  $this->model->cpu,
            'ram'   =>  $this->model->ram,
            'auto_backup_interval'  =>  'none',
            'auto_backup_time'  =>  'none',
            'iaas_compute_pool_id'  =>  $this->model->iaas_compute_pool_id,
            'iaas_compute_member_id'    =>  $this->model->iaas_compute_member_id,
            'iaas_cloud_node_id'  =>  $this->model->iaas_cloud_node_id
        ]);

        $this->setProgress(60, 'Fixing the cloned vm name.');

        VirtualMachinesXenService::fixName($clonedVm);

        $computeMember = VirtualMachinesService::getComputeMember($clonedVm);

        $this->setProgress(65, 'Mounting default backup repository.');

        $backupRepo = ComputeMembersService::getDefaultBackupRepository($computeMember);

        ComputeMemberXenService::mountRepository($computeMember, $backupRepo);

        $this->setProgress(75, 'Removing all the VIFs of cloned VM.');

        $vifs = VirtualMachinesXenService::getVifs($clonedVm);

        foreach ($vifs as $vif) {
            VirtualMachinesXenService::destroyVif($clonedVm, $vif['uuid']);
        }

        $this->setProgress(80, 'Exporting to the default backup repository.');

        BackupService::setBackupState($vmBackup, 'running');

        $backupResult = VirtualMachinesXenService::exportToRepository($clonedVm, $backupRepo);

        BackupService::setBackupState($vmBackup, 'backed-up');

        $backupEnds = Carbon::now();
        $backupDiff = $backupEnds->diffInSeconds($backupStarts);

        $vmBackup->update([
            'path'  =>  $backupResult['path'],
            'filename'  =>  $backupResult['filename'],
            'status'    =>  'backed-up',
            'backup-type'   =>  'full-backup',
            'iaas_repository_id'    =>  $backupRepo->id
        ]);

        $repoImage = RepositoryImagesService::create([
            'iaas_repository_id'    =>  $backupRepo->id,
            'name'                  =>  'Backup of ' . $this->model->name,
            'filename'              =>  $backupResult['filename'],
            'path'                  =>  $backupResult['path'],
            'is_iso'                =>  false,
            'is_public'             =>  false,
            'ram'                   =>  $this->model->ram,
            'cpu'                   =>  $this->model->cpu,
            'default_username'  =>  $this->model->username,
            'default_password'  =>  VirtualMachinesService::getRawPasswordById($this->model->id),
            'is_virtual_machine_image'     =>  true,
            'os'        =>  $this->model->os,
            'distro'    =>  $this->model->distro,
            'version'   =>  $this->model->version,
            'iaas_virtual_machine_id'   =>  $this->model->id,
            'iam_account_id'        =>  $this->model->iam_account_id,
            'iam_user_id'           =>  $this->model->iam_user_id
        ]);

        //  Updating the vm backup to understand where is the image.
        //  In the future we will be removing this table (most probably)
        $vmBackup->updateQuietly([
            'iaas_repository_image_id'  =>  $repoImage->id
        ]);

        RepositoryImagesService::updateRepoSize($repoImage);

        $this->setProgress(90, 'VM exported. It took: ' . $backupDiff . ' seconds.');

        Events::fire('backed-up:NextDeveloper\IAAS\VirtualMachines', $this->model);

        VirtualMachinesXenService::destroyVm($clonedVm);

        $clonedVm->delete();

        $this->setProgress(100, 'Virtual machine backup finished');
    }
}
