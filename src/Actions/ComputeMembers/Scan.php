<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\NetworkMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action will scan compute member and sync all findings. Its the same as initiate but without the auto discovery.
 */
class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanned:NextDeveloper\IAAS\ComputeMembers'
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        UserHelper::setAdminAsCurrentUser();

        $this->model = $computeMember;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        //  Dispatch on ComputePools.virtualization, same precedent as
        //  Jobs/GarbageCollectors/CollectGarbageNetworks.php and Actions/StorageVolumes/Scan.php -
        //  these XenService calls have no capability-interface equivalent yet (see
        //  docs/hypervisor-driver-architecture.md) and only make sense for SSH-reachable
        //  Xen-family hosts, not external-provider compute members (e.g. digitalocean-api).
        $isXenFamily = in_array($this->model->computePools?->virtualization, [
            'xenserver-8.2', 'xenserver-8.2-ssh', 'xcp-ng-8.2', 'xcp-ng-8.2-ssh',
        ], true);

        if ($isXenFamily) {
            $runningTasks = ComputeMemberXenService::getRunningTasks($this->model);

            foreach ($runningTasks as $task) {
                if(Str::contains($task['name-label'], 'import', true)) {
                    $this->setFinished('There is an import process for this compute member, therefore I cannot ' .
                        'scan. If I continue to scan I will create wrong data in database.');
                    return;
                }
            }
        }

        $driver = app(VirtualMachineManager::class)->getAdapterForComputeMember($this->model);
        $canSyncViaDriver = $driver instanceof HostSyncInterface;

        if($this->shouldRunCheckpoint(10)) {
            $canSyncViaDriver ? $this->model = $driver->syncMember($this->model) : ComputeMemberXenService::updateMemberInformation($this->model);
            $this->setProgress(10, 'Updating compute member information');
        }

        if($this->shouldRunCheckpoint(15) && $isXenFamily) {
            ComputeMemberXenService::removeDeletedVlans($this->model);
            $this->setProgress(15, 'Removing vlans which are deleted from compute member');
        }

        if($this->shouldRunCheckpoint(20)) {
            $canSyncViaDriver ? $this->model = $driver->syncInterfaces($this->model) : ComputeMemberXenService::updateInterfaceInformation($this->model);
            $this->setProgress(20, 'Updating compute member network interface information');
        }

        if($this->shouldRunCheckpoint(30) && $isXenFamily) {
            ComputeMemberXenService::updateMissingVlans($this->model);
            $this->setProgress(30, 'Updating compute member storage repository information');
        }

        if($this->shouldRunCheckpoint(40)) {
            $canSyncViaDriver ? $this->model = $driver->syncNetworks($this->model) : ComputeMemberXenService::updateNetworkInformation($this->model);
            $this->setProgress(40, 'Updating compute member bridges/networks information');
        }

        if($this->shouldRunCheckpoint(60)) {
            $canSyncViaDriver ? $this->model = $driver->syncStorageVolumes($this->model) : ComputeMemberXenService::updateStorageVolumes($this->model);
            $this->setProgress(60, 'Updating compute member storage volume information');
        }

        if($this->shouldRunCheckpoint(65)) {
            (new \NextDeveloper\IAAS\Actions\ComputeMembers\ScanVirtualMachines($this->model))->handle();
            $this->setProgress(65, 'Updating VMs in compute member:');
        }

        if($this->shouldRunCheckpoint(70)) {
            $canSyncViaDriver ? $this->model = $driver->syncMember($this->model) : ComputeMemberXenService::updateMemberInformation($this->model);
            $this->setProgress(70, 'Updating compute member resources');
        }

        //  Not routed through VirtualMachineManager: connection-info sync and network-member
        //  mirroring have no capability interface yet - see docs/hypervisor-driver-architecture.md.
        //  Gated on $isXenFamily, same as the other legacy calls above.
        if($this->shouldRunCheckpoint(80) && $isXenFamily) {
            ComputeMemberXenService::updateConnectionInformation($this->model);
            $this->setProgress(80, 'Updating network information');
        }

        if($this->shouldRunCheckpoint(90) && $isXenFamily) {
            NetworkMemberXenService::createNetworkMemberFromComputeMember($this->model);
            $this->setProgress(90, 'Creating network member');
        }

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member scanned and synced');
    }
}
