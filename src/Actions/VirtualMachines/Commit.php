<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\MetaHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Contracts\ProvisioningCapableInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Jobs\VirtualMachines\GenerateCloudInitImage;
use NextDeveloper\IAAS\ProvisioningAlgorithms\ComputeMembers\UtilizeComputeMembers;
use NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes\UtilizeStorageVolumes;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
use NextDeveloper\IAAS\Services\IpAddressesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Converts a draft (or pending-update) virtual machine into a live one on its assigned
 * hypervisor. Triggered whenever a VM needs to go from "configured in the database" to
 * "actually exists on a compute member" - this covers both the very first deploy (import)
 * and re-committing a change to an already-live VM (e.g. adding a disk).
 *
 * All hypervisor-touching work goes through VirtualMachineManager's driver interfaces
 * (see docs/hypervisor-driver-architecture.md) - this class itself contains no
 * hypervisor-specific code, only the provisioning sequence and the DB/algorithm logic
 * (compute member selection, storage volume selection, IP assignment) that has nothing
 * to do with which hypervisor a VM lands on.
 */
class Commit extends AbstractAction
{
    public const EVENTS = [
        'commiting:NextDeveloper\IAAS\VirtualMachines',
        'committed:NextDeveloper\IAAS\VirtualMachines',
        'commit-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public $timeout = 3600;

    public const PARAMS = [
        'is_lazy_deploy'  =>  'boolean',
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        //  Lazy deploy is the default: on the very first commit of a VM, this lets the
        //  import run without blocking on the hypervisor (see importVirtualMachine()) -
        //  the action returns immediately and is resumed later once the import finishes.
        if($params) {
            if(!array_key_exists('is_lazy_deploy', $params)) {
                $params['is_lazy_deploy'] = true;
            }
        } else {
            $params['is_lazy_deploy'] = true;
        }

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Committing virtual machine...');

        if(array_key_exists('is_lazy_deploy', $this->params)) {
            if($this->params['is_lazy_deploy'] == true) {
                $this->setProgress(0, 'Lazy deploying virtual machine...');
            }
        }

        //  ----- Guard clauses: refuse to commit a VM that's lost, deleted, or locked -----

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot commit the configuration for the virtual machine because it is locked.', $this->model);
            $this->setFinished('Virtual machine is locked, therefore I cannot continue.');
            return;
        }

        $vm = $this->model;

        //$vm = VirtualMachinesService::fixUsername($vm);
        $vm = VirtualMachinesService::fixHostname($vm);

        //  Cloud-init needs to reflect the current DB config even before the VM exists on
        //  the hypervisor, since importFromImage() attaches it as part of the import.
        (new GenerateCloudInitImage($vm))->handle();

        if (!$vm->is_draft && !$vm->is_pending_update) {
            $this->setProgress(100, 'Virtual machine is not in draft or pending update state');
            return;
        }

        if($vm->is_pending_update) {
            $vm->updateQuietly([
                'status'    =>  'updating'
            ]);
        } else {
            $vm->updateQuietly([
                'status'    =>  'deploying'
            ]);
        }

        //  ----- Stage 1: import (first commit only - re-commits already have a hypervisor_uuid) -----

        if (!$vm->hypervisor_uuid) {
            $this->setProgress(10, 'Importing virtual machine to the related compute member');
            $this->importVirtualMachine(10);

            if($this->params['is_lazy_deploy']) {
                //  Lazy deploy: the import is running asynchronously on the hypervisor side.
                //  Stop here - VirtualMachinesService::finalizeCommit() re-dispatches this
                //  action once the hypervisor reports the import is done, and execution
                //  resumes from the point below.
                $this->setFinished('Lazy deploying virtual machine.');
                Log::info(__METHOD__ . ' Lazy deploying virtual machine with data: ' . print_r($vm, true));
                return;
            }
        }

        //  ----- Stage 2: everything below runs once hypervisor_uuid is set - either
        //  immediately above (non-lazy import), on a re-commit, or on the resumed run
        //  triggered by finalizeCommit() after a lazy import completes. -----

        $vm = $this->model->fresh();

        Log::info(__METHOD__ . ' Lazy deploying, STEP 2,  virtual machine with data: ' . print_r($vm, true));

        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $repoImage = RepositoryImages::withoutGlobalScopes()->where('id', $vm->iaas_repository_image_id)->first();
        $repo = Repositories::withoutGlobalScopes()->where('id', $repoImage->iaas_repository_id)->first();

        $this->setProgress(19, 'Unmounting repository from compute member');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][19] | Unmounting repository from compute member');

        $provisioningDriver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapter($vm),
            ProvisioningCapableInterface::class
        );

        //  The repo is only needed for the import itself - unmount it now that it's done.
        $provisioningDriver->unmountRepository($computeMember, $repo);
        //  Exposes the API endpoint URL to the guest via its metadata channel, so in-guest
        //  tooling (e.g. the VM agent) knows where to call back to.
        $provisioningDriver->injectGuestMetadata($vm, 'api', config('app.url'));
        //  Hypervisor-side display name should match our internal uuid, not whatever
        //  default name the import gave it.
        $provisioningDriver->renameVirtualMachine($vm);

        $vm->update([
            'state' =>  'configuring'
        ]);

        $this->postImportConfiguration(
            vm: $vm,
            step: 14
        );

        Log::info(__METHOD__ . ' Lazy deploying, STEP 3, virtual machine with data: ' . print_r($vm, true));

        //  ----- Stage 3: sync the compute member's own host-level info -----

        $hostDriver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapterForComputeMember($computeMember),
            HostSyncInterface::class
        );

        $hostDriver->syncMember($computeMember);

        //  ----- Stage 4: reconcile CPU/RAM, disks, and networking against DB config -----

        $this->setProgress(20, 'Setting CPU and RAM');
        $this->setCpuRam();

        $this->setProgress(40, 'Syncing disk configuration');
        $this->setupDisks();

        $this->setProgress(70, 'Syncing network configuration');
        $this->setupNetworking();

        $this->setupIp();

        //  Cloud-init may need updating again now that networking/disks are finalized.
        (new GenerateCloudInitImage($this->model))->handle();

        //  ----- Stage 5: report the real power state -----

        //  We ask the hypervisor for the real power state instead of assuming 'halted', because committing a
        //  pending update (e.g. adding a disk) can happen while the VM is still running.
        $vmParams = app(VirtualMachineManager::class)->getHypervisorData($vm);

        $vm->update([
            'status' => $vmParams['power-state'] ?? 'halted',
            'is_pending_update' => false,
        ]);

        //  Buranın değişmesi lazım, zira bunun boot_after_commit olması lazım.
        if(MetaHelper::get($vm, 'boot_after_deploy')) {
            if(MetaHelper::get($vm, 'boot_after_deploy') == true) {
                dispatch(new Start($vm));
            }
        }

        $this->setProgress(100, 'Virtual machine initiated');
    }

    /**
     * Resolves a driver capability or throws clearly if the VM's hypervisor driver
     * doesn't implement it. VM provisioning has no legacy fallback path, so a missing
     * capability should fail loudly here rather than silently doing nothing (which is
     * what a dead switch/case dispatch would otherwise do for an unrecognized platform).
     */
    private function requireCapability(?VirtualMachineAdapterInterface $driver, string $interface): object
    {
        if (!$driver instanceof $interface) {
            throw new \RuntimeException("The hypervisor driver for this VM does not implement {$interface}, which VM provisioning requires.");
        }

        return $driver;
    }

    /**
     * Sets the VM's CPU core count and RAM on the hypervisor to match the DB config.
     */
    private function setCpuRam(): void
    {
        $driver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapter($this->model),
            ResizeCapableInterface::class
        );

        $driver->resize($this->model, $this->model->cpu, $this->model->ram);
    }

    /**
     * Auto-assigns an IP to any network card whose network has a DHCP server and doesn't
     * already have one, per its `auto_add_ip_v4` setting. Pure DB/algorithm work - not a
     * hypervisor call.
     */
    private function setupIp()
    {
        $vifs = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $this->model->id)
            ->get();

        foreach ($vifs as $vif) {
            $network = VirtualNetworkCardsService::getConnectedNetwork($vif);

            if(!$network->iaas_dhcp_server_id) {
                // if we dont have a dhcp server then we skip this step.
                continue;
            }

            //  This means we have a dhcp server and the network is managed with that DHCP server

            $ipList = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_network_card_id', $vif->id)
                ->get();

            $addIp = MetaHelper::get($vif, 'auto_add_ip_v4');

            $addRandomIp = false;

            if(array_key_exists('enabled', $addIp)) {
                $addRandomIp = $addIp['enabled'];
            }

            Log::info('[VM Commit][Setup IP] IP count for VIF:' . count($ipList)
                . ' auto_add_ip_v4: ' . $addRandomIp);

            Log::debug('[VM Commit][Setup IP] The VIF is: ' . $vif->id . ' and the AddIp directive for this VIF is ' . $addRandomIp . '. The network is: ' . $vif->iaas_network_id);

            //  If there is no IP in the card and auto_add_ip_v4 is true
            if($addIp && !count($ipList)) {
                $nextAvailableIp = IpAddressesService::getNextIpAvailable($network);

                Log::info('[VM Commit][Setup IP] The next available IP is: ' . $nextAvailableIp);

                VirtualNetworkCardsService::assignIpToCard($nextAvailableIp, $vif);
            }
        }
    }

    /**
     * Reconciles the VM's network card configuration against the hypervisor's live VIF
     * list (creates/syncs/destroys as needed) - see
     * ProvisioningCapableInterface::reconcileNetworkConfiguration().
     */
    private function setupNetworking(): void
    {
        $driver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapter($this->model),
            ProvisioningCapableInterface::class
        );

        $driver->reconcileNetworkConfiguration($this->model);
    }

    /**
     * Reconciles the VM's disk/CD configuration against the hypervisor's live disk list
     * (creates/syncs/destroys as needed) - see
     * ProvisioningCapableInterface::reconcileDiskConfiguration().
     */
    private function setupDisks(): void
    {
        $driver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapter($this->model),
            ProvisioningCapableInterface::class
        );

        $driver->reconcileDiskConfiguration($this->model);
    }

    /**
     * First-commit-only: picks a compute member and storage volume for the VM (pure
     * DB/algorithm work - has nothing to do with which hypervisor is involved), then hands
     * off to the driver to actually import the VM from its repository image.
     */
    private function importVirtualMachine($step)
    {
        //  We know that this virtual machine is in draft state and it does not have a record in hypervisor
        //  So we can initiate the virtual machine here
        $vm = $this->model;

        $machineImage = RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_repository_image_id)
            ->first();

        $repositoryServer = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $machineImage->iaas_repository_id)
            ->first();

        $this->setProgress($step + 1, 'Finding the best compute member');

        $computePool = ComputePools::where('id', $vm->iaas_compute_pool_id)->first();

        if($this->model->iaas_compute_member_id) {
            $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $vm->iaas_compute_member_id)
                ->first();
        } else {
            $computeMember = (new UtilizeComputeMembers($computePool))->calculate(
                $vm->ram,
                $vm->cpu
            );
        }

        $computeMember->used_ram += ($vm->ram / 1024);
        $computeMember->saveQuietly();

        $storageVolume = null;

        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 1 . '] | Found the best compute member: ' . $computeMember->name);

        $this->model->update([
            'iaas_compute_member_id' => $computeMember->id,
        ]);

        $this->setProgress($step + 2, 'Finding the best storage volume for your virtual machine.');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 2 . '] | Finding the best storage volume for your virtual machine.');

        //  Checking disk configuration here. At this moment we will only implement the null disk formation actually.
        //  Later we will implement the deployment of disks by looking at disk formation.

        //  Checking if we already decided a storage volume for this VM
        $vmDisk = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->first();

        if($vmDisk->iaas_storage_volume_id) {
            $storageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $vmDisk->iaas_storage_volume_id)
                ->first();
        } else {
            //  Check if the compute pool is one or star
            if ($computePool->pool_type == 'one') {
                $this->setProgress($step + 3, 'Since the pool type is "one" we will be deploying this server to a local storage.');
                Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 3 . '] | Since the pool type is "one" we will be deploying this server to a local storage.');

                $computeMemberStorageVolumes = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_compute_member_id', $computeMember->id)
                    ->where('is_local_storage', true)
                    ->first();

                $storageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                    ->where('id', $computeMemberStorageVolumes->iaas_storage_volume_id)
                    ->first();
            } else {
                $this->setProgress($step + 3, 'Since the pool type is "star" we will be deploying this server to an ssd or nvme storage.');
                Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 3 . '] | Since the pool type is "star" we will be deploying this server to an ssd or nvme storage.');
                // If we don't have a storage pool here, we will be choosing the SSD pool.
                // Why ? because I wanted to do like that :D

                $storagePool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                    ->where('storage_pool_type', 'ssd')
                    ->first();

                if (!$storagePool)
                    $storagePool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
                        ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                        ->where('storage_pool_type', 'nvme')
                        ->first();

                if (!$storagePool)
                    $this->setFinishedWithError('There is no SSD or NVMe storage pool in this Cloud Node!. Please contact support.');

                $storageVolume = (new UtilizeStorageVolumes($storagePool))->calculate($computeMember, 20);
            }
        }

        //  Here I am putting this as control because if the pool type is one and we dont have a storage volume then we have a problem
        if(!$storageVolume && $computePool->pool_type == 'one')
            throw new \Exception('We have a configuration error on compute pool, or there is a problem with ' .
                'the sync. I cannot find the volume that I should find, because this is a One type pool and there' .
                ' should be an on-board volume in hypervisor. Also there can be another reasons, which are maybe ' .
                'the storage volume is set to be not alive, which means we can be doomed! OR it is not set a sstorage');

        $vm->update([
            'iaas_compute_member_id' => $computeMember->id,
            'iaas_repository_image_id' => $machineImage->id
        ]);

        //  The driver decides internally how to avoid blocking on a long-running import
        //  (e.g. backgrounding it and reporting completion asynchronously) when
        //  is_lazy_deploy is true - see ProvisioningCapableInterface::importFromImage().
        $driver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapter($vm),
            ProvisioningCapableInterface::class
        );

        $driver->importFromImage(
            $vm,
            $computeMember,
            $repositoryServer,
            $storageVolume,
            $machineImage,
            $this->params['is_lazy_deploy']
        );

        $this->setProgress($step + 9, 'Virtual machine imported');
    }

    /**
     * After import, fetches the VM's parameters from the hypervisor by its native
     * reference and records them (hypervisor_uuid/hypervisor_data/state), marking the VM
     * as no longer draft. Fires the "imported" events other parts of the system listen for
     * (e.g. to know a VM/compute member just came online).
     */
    private function postImportConfiguration($vm, $step)
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);
        $uuid = $vm->hypervisor_uuid;

        if(!$uuid) {
            //  This means that we are running postImportConfiguration because the VM is imported already and
            //  we need to rerun the import process, and running the import again.

            //  Here we are assuming that the uuid is pushed by the hypervisor to API by triggering the API when
            //  the import is finished. Therefore the hypervisor_uuid should be in the $vm object.

            if(!$vm->hypervisor_uuid) {
                $this->setFinishedWithError('We expected this VM (' . $vm->uuid . ') to be imported, ' .
                    'and hypervisor_uuid should be set. But that is not the case, therefore we are stopping ' .
                    'for import.');
            }
        }

        $this->setProgress($step + 6, 'Updating virtual machine parameters');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 6 . '] | Updating virtual machine parameters');

        $driver = $this->requireCapability(
            app(VirtualMachineManager::class)->getAdapter($vm),
            ProvisioningCapableInterface::class
        );

        $vmParams = $driver->getVmParametersByRef($computeMember, $uuid);

        $vm->update([
            'hypervisor_uuid' => $vmParams['uuid'],
            'hypervisor_data' => $vmParams,
            'state' => $vmParams['power-state'],
            'is_draft' => false,
            'status' => 'halted'
        ]);

        Events::listen('imported:NextDeveloper\IAAS\VirtualMachines', $vm);
        Events::listen('imported-virtual-machine:NextDeveloper\IAAS\ComputeMembers', $computeMember);
    }
}
