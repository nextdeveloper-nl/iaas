<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\MetaHelper;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualNetworkCards\Attach;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\ProvisioningAlgorithms\ComputeMembers\UtilizeComputeMembers;
use NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes\UtilizeStorageVolumes;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\IpAddressesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action converts a draft virtual machine to a live virtual machine. This action should be triggered when the
 * virtual machine is in draft state and needs to go live. If the virtual machine state is not draft this action will
 * cancel itself.
 */
class Commit extends AbstractAction
{
    public const EVENTS = [
        'commiting:NextDeveloper\IAAS\VirtualMachines',
        'committed:NextDeveloper\IAAS\VirtualMachines',
        'commit-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    private $diskConfiguration;

    private $networkConfiguration;

    private $computePool;

    public $timeout = 3600;

    public const PARAMS = [
        'is_lazy_import'  =>  'boolean',
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        if($params) {
            if(!array_key_exists('is_lazy_import', $params)) {
                $params['is_lazy_import'] = false;
            }
        } else {
            $params['is_lazy_import'] = false;
        }

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Committing virtual machine...');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        $vm = $this->model;

        $vm = VirtualMachinesService::fixUsername($vm);
        $vm = VirtualMachinesService::fixHostname($vm);

        if (!$vm->is_draft && $vm->status != 'pending-update') {
            $this->setProgress(100, 'Virtual machine is not in draft or pending update state');
            return;
        }

        if($vm->status == 'pending-update') {
            $vm->updateQuietly([
                'status'    =>  'updating'
            ]);
        } else {
            $vm->updateQuietly([
                'status'    =>  'deploying'
            ]);
        }

        $this->computePool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_pool_id)
            ->first();

        //  Here we will import the virtual machine
        if (!$vm->hypervisor_uuid) {
            $this->setProgress(10, 'Importing virtual machine to the related compute member');
            $this->importVirtualMachine(10);
        } else {
            $this->setProgress(10, 'Virtual machine already imported, ' .
                'skipping to disk configuration and continue with the configuration');

            $computeMember = VirtualMachinesService::getComputeMember($vm);

            $this->postImportConfiguration(
                vm: $vm,
                computeMember: $computeMember,
                step: 14
            );

            ComputeMemberXenService::updateMemberInformation($computeMember);
        }

        //  We need to update CPU and RAM
        $this->setProgress(20, 'Setting CPU and RAM');
        $this->setCpuRam();

        $this->setupDisks(40);

        $this->setupNetworking(70);

        $this->setupIp(80);

        $vm->update([
            'status' => 'halted',
        ]);

        //  Buranın değişmesi lazım, zira bunun boot_after_commit olması lazım.
        if(MetaHelper::get($vm, 'boot_after_deploy')) {
            if(MetaHelper::get($vm, 'boot_after_deploy') == true) {
                dispatch(new Start($vm));
            }
        }

        $this->setProgress(100, 'Virtual machine initiated');
    }

    private function setCpuRam()
    {
        switch ($this->computePool->virtualization) {
            case 'xenserver-8.2':
                VirtualMachinesXenService::setCPUCore($this->model, $this->model->cpu);
                VirtualMachinesXenService::setRam($this->model, $this->model->ram);
                break;
        }
    }

    private function setupIp($step)
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

    private function setupNetworking($step)
    {
        switch ($this->computePool->virtualization) {
            case 'xenserver-8.2':
                $this->setupXenNetworking($step);
                break;
        }
    }

    /**
     * In this function we will be setting up the network card
     *
     * @param $step
     * @return void
     */
    private function setupXenNetworking($step)
    {
        $vm = $this->model;

        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        //  Checking if the virtual machine actually has a VIF. If has we are syncing those vifs.
        $vifs = VirtualMachinesXenService::getVifs($vm);

        $syncedVifs = [];

        foreach ($vifs as $vif) {
            if(!count($vif))
                continue;

            foreach ($netConfig as $config) {
                if($config->device_number == $vif['device']) {
                    $this->syncXenVif($vif, $config);

                    $syncedVifs[] = $vif['uuid'];
                }
            }
        }

        foreach ($vifs as $vif) {
            if(!count($vif))
                continue;

            if(!in_array($vif['uuid'], $syncedVifs)) {
                VirtualMachinesXenService::destroyVif($vm, $vif['uuid']);
            }
        }

        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        foreach ($netConfig as $config) {
            //  Here we check if the VIF not exists. If not exists hypervisor_uuid is null
            if($config->hypervisor_uuid == null) {
                (new Attach($config))->handle();
            }
        }
    }

    private function syncXenVif($vif, $config) {
        $params = VirtualMachinesXenService::getVifParams($this->model, $vif['uuid']);

        $vif = $params[0];

        $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('network_uuid', $vif['network-uuid'])
            ->first();

        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('vlan', $cmni->vlan)
            ->first();

        $config->update([
            'hypervisor_uuid'   => $vif['uuid'],
            'hypervisor_data'   => $vif,
            'mac_addr'          => $vif['MAC'],
            'iaas_network_id'   =>  $network ? $network->id : null,
            'bandwitdh_limit'   =>  -1,
            'is_draft'          =>  false
        ]);
    }

    private function setupDisks($step)
    {
        $computePool = ComputePools::where('id', $this->model->iaas_compute_pool_id)->first();

        switch ($computePool->virtualization) {
            case 'xenserver-8.2':
                $this->setupXenDisks($step);
                break;
        }
    }

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

        $computeMember->used_ram += $vm->ram;
        $computeMember->saveQuietly();

        $storageVolume = null;

        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 1 . '] | Found the best compute member: ' . $computeMember->name);

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

        switch ($computePool->virtualization) {
            case 'xenserver-8.2':
                $uuid = $this->importXenServer($vm, $computeMember, $repositoryServer, $storageVolume, $machineImage, $step);
                $this->postImportConfiguration($vm, $computeMember, $uuid, $machineImage, $repositoryServer, $step);
                break;
        }

        ComputeMemberXenService::updateMemberInformation($computeMember);

        $this->setProgress($step + 9, 'Virtual machine imported');
    }

    private function importXenServer($vm, $computeMember, $repo, $volume, $image, $step = 0)
    {
        $this->setProgress($step + 4, 'Mounting repository to compute member');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 4 . '] | Mounting repository to compute member');
        ComputeMemberXenService::mountVmRepository($computeMember, $repo);

        $this->setProgress($step + 5, 'Importing virtual machine image');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 5 . '] | Importing virtual machine image');

        $uuid = '';

        if($this->params['is_lazy_import']) {
            $uuid = ComputeMemberXenService::importVirtualMachine(
                computeMember: $computeMember,
                volume: $volume,
                image: $image,
                isBackgroundImport: $this->params['is_lazy_import'],
                vmUuid: $vm->uuid
            );
        } else {
            $uuid = ComputeMemberXenService::importVirtualMachine(
                computeMember: $computeMember,
                volume: $volume,
                image: $image,
            );
        }

        return $uuid;
    }

    private function postImportConfiguration($vm, $computeMember, $uuid = null, $image = null, $repo = null, $step)
    {
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
        $vmParams = VirtualMachinesXenService::getVmParametersByUuid($computeMember, $uuid);

        $vm->update([
            'hypervisor_uuid' => $vmParams['uuid'],
            'hypervisor_data' => $vmParams,
            'iaas_compute_member_id' => $computeMember->id,
            'state' => $vmParams['power-state'],
            'os' => $image->os,
            'distro' => $image->distro,
            'version' => $image->version,
            'is_draft' => false,
            'status' => 'halted'
        ]);

        Events::listen('imported:NextDeveloper\IAAS\VirtualMachines', $vm);
        Events::listen('imported-virtual-machine:NextDeveloper\IAAS\ComputeMembers', $computeMember);

        $this->setProgress($step + 9, 'Unmounting repository from compute member');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 9 . '] | Unmounting repository from compute member');
        $result = ComputeMemberXenService::unmountVmRepository($computeMember, $repo);

        ComputeMemberXenService::setVmXenstoreData('api', config('app.url'), $vm, $computeMember);
        ComputeMemberXenService::renameVirtualMachine($computeMember, $vm);
    }

    private function setupXenDisks($step = 0)
    {
        $vm = $this->model;
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        ComputeMemberXenService::renameVirtualMachine($computeMember, $vm);

        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        $this->setProgress($step + 3, 'Got the disk configuration of the virtual machine.');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 1 . '] | Got the disk configuration of the virtual machine.');

        //  Check if imported VM has a disk already
        $disks = VirtualMachinesXenService::getVmDisks($vm);

        $this->setProgress($step + 4, 'Syncing the disks we have.');
        Log::info(__METHOD__ . ' [' . $this->getActionId() . '][' . $step + 3 . '] | Syncing the disks we have.');

        $syncedDisks = [];

        foreach ($disks as $disk) {
            $connectionParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);

            foreach ($diskConfig as $config) {
                //  If the userdevice and device_number are equal, we will sync this disk.
                if ($connectionParams['userdevice'] == $config['device_number']) {
                    $this->setProgress($step + 5, 'Syncing the disks we have.');
                    $this->syncDisk($config, $disk);
                    $syncedDisks[] = $disk['uuid'];
                }
            }
        }

        $unsyncedDisks = [];

        $this->setProgress($step + 5, 'Finding the disks/cdroms we dont want on VM.');

        foreach ($disks as $disk) {
            if (!in_array($disk['uuid'], $syncedDisks)) {
                $unsyncedDisks[] = $disk;
            }
        }

        $this->setProgress($step + 6, 'Removing unwanted disks/cdroms.');

        foreach ($unsyncedDisks as $disk) {
            if($disk['vdi-uuid'] === '<not in database>') {
                VirtualDiskImageXenService::destroyCdrom($vm->uuid, $computeMember);
            } else {
                VirtualDiskImageXenService::destroyDisk($disk['vdi-uuid'], $computeMember);
            }
        }

        //  After we finish syncing the disks, we will check if we have any disk configuration that is not synced.

        Log::info(__METHOD__ . ' | Checking if we have draft disks that we need to handle');
        $this->setProgress($step + 1, 'Looking if we have new disks that we need to create or attach.');

        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        //  Now we need to create the disks that are in draft state
        foreach ($diskConfig as $disk) {
            //  If we have a draft disk this means that we have a disk that we need to create
            if($disk->is_draft) {
                $disk = VirtualDiskImageXenService::create($disk);
                $disk = VirtualDiskImageXenService::attach($disk);

                $disk->updateQuietly([
                    'is_draft'  =>  false
                ]);
            }
        }

        $this->setProgress($step + 9, 'Disk sync finished.');
    }

    private function syncDisk($config, $disk) {
        $vm = $this->model;
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        $expectedDiskSize = $config->size;

        //  We are making the resize first because we need to get the disk parameters after the resize.
        //  And from there we will understand if the disk is resized or not.

        $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);

        //  If this is not a CDROM
        if($vbdParams['type'] != 'CD') {
            VirtualDiskImageXenService::resize($disk['vdi-uuid'], $computeMember, $config->size);
            $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);
        }

        $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($disk['vdi-uuid'], $computeMember);

        $diskVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $disk['uuid'])
            ->first();

        if($vbdParams['type'] != 'CD') {
            //  This means that this is not a CDROM. If this is a cdrom we don't need to check the size.
            if($config->size != $diskParams['virtual-size']) {
                StateHelper::setState($config, 'disk_cannot_resized', 'Disk cannot resized. Current size is: ' . $diskParams['virtual-size'], 'warn');
            }
        }

        $data = [
            'name' => $vbdParams['type'] !== 'CD' ? $config->name : 'CDROM',
            'size' => $vbdParams['type'] !== 'CD' ? $diskParams['virtual-size'] : 0,
            'physical_utilisation' => $vbdParams['type'] !== 'CD' ? $diskParams['physical-utilisation'] : 0,
            'iaas_storage_volume_id' => $vbdParams['type'] !== 'CD' ?? $diskVolume->iaas_storage_volume_id,
            'iaas_virtual_machine_id' => $vm->id,
            'device_number' => $vbdParams['userdevice'],
            'is_cdrom' => $vbdParams['type'] === 'CD',
            'hypervisor_uuid' => $vbdParams['vdi-uuid'],
            'hypervisor_data' => $disk,
            'iam_account_id' => $vm->iam_account_id,
            'iam_user_id' => $vm->iam_user_id,
            'is_draft' => false,
        ];

        $config->update($data);
    }
}
