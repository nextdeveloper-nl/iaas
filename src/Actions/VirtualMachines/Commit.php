<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use GPBMetadata\Google\Api\Auth;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
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

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Committing virtual machine...');

        $vm = $this->model;

        if (!$vm->is_draft && $vm->status != 'pending-update') {
            $this->setProgress(100, 'Virtual machine is not in draft or pending update state');
            return;
        }

        //  Here we will import the virtual machine
        if (!$vm->hypervisor_uuid) {
            $this->importVirtualMachine(10);
        } else {
            $this->setProgress(10, 'Virtual machine already imported, ' .
                'skipping to disk configuration');
        }


        //$this->setupDisks(40);

        $this->setupNetworking(70);

        $vm->update([
            'status' => 'halted',
        ]);

        $this->setProgress(100, 'Virtual machine initiated');
    }

    private function setupNetworking($step)
    {
        $computePool = ComputePools::where('id', $this->model->iaas_compute_pool_id)->first();

        switch ($computePool->virtualization) {
            case 'xenserver-8.2':
                $uuid = $this->setupXenNetworking($step);
                break;
        }
    }

    private function setupXenNetworking($step)
    {
        $vm = $this->model;

        $netConfig = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

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
            if($config->hypervisor_uuid == null) {
                $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                    ->where('id', $config->iaas_network_id)
                    ->first();

                $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                    ->where('vlan', $network->vlan)
                    ->first();

                $result = VirtualMachinesXenService::createVif($vm, $cmni->network_uuid, $config->device_number);

                $params = VirtualMachinesXenService::getVifParams($vm, $result);

                $vif = $params[0];

                $config->update([
                    'hypervisor_uuid'   => $vif['uuid'],
                    'hypervisor_data'   => $vif,
                    'mac_addr'          => $vif['MAC'],
                    'iaas_network_id'   =>  $network ? $network->id : null,
                    'bandwitdh_limit'   =>  -1,
                    'is_draft'          =>  false
                ]);
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
                $uuid = $this->setupXenDisks($step);
                break;
        }
    }

    private function importVirtualMachine($step)
    {
        //  We know that this virtual machine is in draft state and it does not have a record in hypervisor
        //  So we can initiate the virtual machine here
        $vm = $this->model;

        $machineImage = RepositoryImages::where('id', $vm->iaas_repository_image_id)->first();

        $repositoryServer = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $machineImage->iaas_repository_id)
            ->first();

        $this->setProgress($step + 1, 'Finding the best compute member');

        $computePool = ComputePools::where('id', $vm->iaas_compute_pool_id)->first();

        $computeMember = (new UtilizeComputeMembers($computePool))->calculate(
            $vm->ram,
            $vm->cpu
        );

        $storageVolume = null;

        $this->setProgress($step + 2, 'Finding the best storage volume for your virtual machine.');

        //  Checking disk configuration here. At this moment we will only implement the null disk formation actually.
        //  Later we will implement the deployment of disks by looking at disk formation.

        //  Check if the compute pool is one or star
        if ($computePool->pool_type == 'one') {
            $this->setProgress($step + 3, 'Since the pool type is "one" we will be deploying this server to a local storage.');

            $storageMember = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
                ->where('ip_addr', $computeMember->ip_addr)
                ->first();

            $storageVolume = StorageVolumes::where('iaas_storage_member_id', $storageMember->id)
                ->where('is_storage', true)
                ->where('is_alive', true)
                ->first();
        } else {
            $this->setProgress($step + 3, 'Since the pool type is "star" we will be deploying this server to an ssd or nvme storage.');
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

        switch ($computePool->virtualization) {
            case 'xenserver-8.2':
                $uuid = $this->importXenServer($vm, $computeMember, $repositoryServer, $storageVolume, $machineImage, $step);
                break;
        }

        $this->setProgress($step + 9, 'Virtual machine imported');
    }

    private function importXenServer($vm, $computeMember, $repo, $volume, $image, $step = 0)
    {
        $this->setProgress($step + 4, 'Mounting repository to compute member');
        ComputeMemberXenService::mountVmRepository($computeMember, $repo);
        $this->setProgress($step + 5, 'Importing virtual machine image');
        $uuid = ComputeMemberXenService::importVirtualMachine($computeMember, $volume, $image);

        $this->setProgress($step + 6, 'Updating virtual machine parameters');
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
        $result = ComputeMemberXenService::unmountVmRepository($computeMember, $repo);

        ComputeMemberXenService::renameVirtualMachine($computeMember, $vm);

        return true;
    }

    private function setupXenDisks($step = 0)
    {
        $vm = $this->model;
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        ComputeMemberXenService::renameVirtualMachine($computeMember, $vm);

        $diskConfig = VirtualDiskImages::where('iaas_virtual_machine_id', $vm->id)->orderBy('id', 'asc')->get();

        $this->setProgress($step + 1, 'Got the disk configuration of the virtual machine.');

        //  Check if imported VM has a disk already
        $disks = VirtualMachinesXenService::getVmDisks($vm);

        $this->setProgress($step + 3, 'Syncing the disks we have.');

        if (!$diskConfig) {
            //  Here this means that we dont have any disk config, so we will directly sync what we have.
            $this->syncDisks($step);
        }

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

        $this->setProgress($step + 5, 'Removing unwanted disks/cdroms.');

        foreach ($unsyncedDisks as $disk) {
            if($disk['vdi-uuid'] === '<not in database>') {
                VirtualDiskImageXenService::destroyCdrom($vm->uuid, $computeMember);
            } else {
                VirtualDiskImageXenService::destroyDisk($disk['vdi-uuid'], $computeMember);
            }
        }
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
