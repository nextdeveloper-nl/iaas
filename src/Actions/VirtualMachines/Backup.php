<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachineBackupsService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Helpers\UserHelper;

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

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

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

        ComputeMemberXenService::mountDefaultBackupRepository($computeMember);

        $this->setProgress(75, 'Removing all the VIFs of cloned VM.');

        $vifs = VirtualMachinesXenService::getVifs($clonedVm);

        foreach ($vifs as $vif) {
            VirtualMachinesXenService::destroyVif($clonedVm, $vif['uuid']);
        }

        $this->setProgress(80, 'Exporting to the default backup repository.');

        $backupResult = VirtualMachinesXenService::exportToDefaultBackupRepository($clonedVm);

        $backupEnds = Carbon::now();
        $backupDiff = $backupEnds->diffInSeconds($backupStarts);

        $vmBackup = VirtualMachineBackupsService::create([
            'name'  =>  'Backup of ' . $this->model->name,
            'path'  =>  $backupResult['path'],
            'filename'  =>  $backupResult['filename'],
            'username'  =>  $this->model->username,
            'password'  =>  $this->model->password,
            'size'      =>  0,
            'ram'       =>  $this->model->ram,
            'cpu'       =>  $this->model->cpu,
            'hash'      =>  0,
            'status'    =>  'backed-up',
            'backup_starts' =>  $backupStarts->timestamp,
            'backup_ends'   =>  $backupEnds->timestamp,
            'backup-type'   =>  'full-backup',
            'iaas_virtual_machine_id'   =>  $this->model->id,
            'iam_account_id'    =>  UserHelper::currentAccount()->id,
            'iam_user_id'   =>  UserHelper::currentUser()->id
        ]);

        $this->setProgress(90, 'VM exported. It took: ' . $backupDiff . ' seconds.');

        Events::fire('backed-up:NextDeveloper\IAAS\VirtualMachines', $this->model);

        VirtualMachinesXenService::destroyVm($clonedVm);

        $clonedVm->delete();

        $this->setProgress(100, 'Virtual machine backup finished');
    }
}
