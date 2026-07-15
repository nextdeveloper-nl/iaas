<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\BackupCapableInterface;
use NextDeveloper\IAAS\Contracts\CloneCapableInterface;
use NextDeveloper\IAAS\Contracts\SnapshotCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action backs up a virtual machine: snapshot it, convert the snapshot to a
 * standalone VM, clone that so the original snapshot can be released quickly, export the
 * clone to the account's default backup repository, then remove the clone.
 *
 * NOTE: handle() was previously an empty stub (this action never actually ran) - this is
 * a from-scratch implementation of the checkpoint sequence already defined below, routed
 * entirely through SnapshotCapableInterface/CloneCapableInterface/BackupCapableInterface.
 * Every individual driver operation has been dry-run tested against real DB data, but the
 * full multi-step orchestration (snapshot -> convert -> clone -> export -> cleanup) has
 * not been exercised end-to-end against a real XenServer host - verify on a test VM
 * before relying on it for production backups. See docs/hypervisor-driver-architecture.md.
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

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Starting the backup process');

        Events::fire('backing-up:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $driver = app(VirtualMachineManager::class)->getAdapter($this->model);

        if (
            !$driver instanceof SnapshotCapableInterface
            || !$driver instanceof CloneCapableInterface
            || !$driver instanceof BackupCapableInterface
        ) {
            $this->failBackup('No driver capable of snapshotting, cloning, and backup export is registered for this compute pool.');
            return;
        }

        try {
            $this->setProgress(10, 'Taking the snapshot of the virtual machine');
            $snapshotVm = $driver->createSnapshot($this->model, 'Backup snapshot of ' . $this->model->name);

            $this->setProgress(20, 'Snapshot is taken, creating the snapshot object.');
            //  $snapshotVm is already the persisted snapshot VirtualMachines row created by
            //  createSnapshot() - nothing further to do at this checkpoint.

            $this->setProgress(30, 'Fixing the name of the snapshot.');
            (new Fix($snapshotVm))->handle();

            $this->setProgress(40, 'Converting Snapshot to VM.');
            $driver->convertSnapshotToVm($snapshotVm);

            $this->setProgress(50, 'Cloning the VM.');
            $clonedVm = $driver->clone($snapshotVm, 'backup-clone-of-' . $this->model->uuid);

            $this->setProgress(55, 'Deleting the snapshot.');
            $driver->deleteSnapshot($this->model, $snapshotVm->uuid);

            $this->setProgress(60, 'Fixing the cloned vm name.');
            (new Fix($clonedVm))->handle();

            $this->setProgress(65, 'Mounting default backup repository.');
            $computeMember = VirtualMachinesService::getComputeMember($clonedVm);
            $driver->mountDefaultBackupRepository($computeMember);

            $this->setProgress(75, 'Removing all the VIFs of cloned VM.');
            $driver->stripAllNetworkCards($clonedVm);

            $this->setProgress(80, 'Exporting to the default backup repository.');
            $exportResult = $driver->exportToDefaultBackupRepository($clonedVm);

            if (!empty($exportResult['error'])) {
                throw new \RuntimeException('Export to backup repository failed: ' . $exportResult['error']);
            }

            $this->setProgress(90, 'VM exported, removing the cloned VM.');
            $driver->delete($clonedVm);

            $this->setProgress(95, 'Removed VM that was cloned.');

            Events::fire('backed-up:NextDeveloper\IAAS\VirtualMachines', $this->model);
            $this->setProgress(100, 'Virtual machine backup finished');
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' | Backup failed for VM ' . $this->model->uuid . ': ' . $e->getMessage());

            $this->failBackup('Backup failed: ' . $e->getMessage());
        }
    }

    private function failBackup(string $message): void
    {
        Events::fire('backup-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setFinishedWithError($message);
    }
}
