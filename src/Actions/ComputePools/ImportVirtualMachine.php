<?php
namespace NextDeveloper\IAAS\Actions\ComputePools;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\ProvisioningCapableInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\AlreadyImportedVirtualMachine;
use NextDeveloper\IAAS\ProvisioningAlgorithms\ComputeMembers\UtilizeComputeMembers;
use NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes\UtilizeStorageVolumes;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\StorageMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action is used to import a virtual machine into a compute pool. You need to provide Storage Pool ID to
 * complete the import process
 */
class ImportVirtualMachine extends AbstractAction
{
    public const EVENTS = [
        'importing:NextDeveloper\IAAS\VirtualMachines',
        'imported:NextDeveloper\IAAS\VirtualMachines',
        'importing-virtual-machine:NextDeveloper\IAAS\ComputePools',
        'imported-virtual-machine:NextDeveloper\IAAS\ComputePools',
        'imported-virtual-machine:NextDeveloper\IAAS\ComputeMembers',
    ];

    public const PARAMS = [
        'iaas_repository_image_id'  =>  'required|exists:iaas_repository_images,uuid',
        'iaas_storage_pool_id'      =>  'required|exists:iaas_storage_pools,uuid',
        'iaas_virtual_machine_id'   =>  'required|exists:iaas_virtual_machines,uuid'
    ];

    private $image;

    private $repository;

    private $storagePool;

    private $computePool;

    private $virtualMachine;

    public function __construct(ComputePools $pool, $params)
    {
        if(array_key_exists(0, $params))
            $params = $params[0];

        $this->model = $pool;
        $this->computePool = $pool;

        $this->image = RepositoryImages::where('uuid', $params['iaas_repository_image_id'])->first();
        $this->storagePool = StoragePools::where('uuid', $params['iaas_storage_pool_id'])->first();
        $this->virtualMachine = VirtualMachines::where('uuid', $params['iaas_virtual_machine_id'])->first();

        if($this->virtualMachine->hypervisor_uuid)
            throw new AlreadyImportedVirtualMachine('According to our database this VM is' .
                ' already imported. If you think' .
                ' this is a mistake, please contact support.');

        $this->queue = 'iaas';

        parent::__construct($params);
    }

    public function handle()
    {
        $this->setProgress(0, 'Importing virtual machine started');

        $this->setProgress(5, 'Finding the repository');

        $this->repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->image->iaas_repository_id)
            ->first();

        $this->setProgress(5, 'Finding the best compute member');

        $computeMember = (new UtilizeComputeMembers($this->computePool))->calculate(
            $this->virtualMachine->ram,
            $this->virtualMachine->cpu
        );

        $this->setProgress(10, 'Finding the best storage volume for the compute member');

        $storageVolume = (new UtilizeStorageVolumes($this->storagePool))->calculate($computeMember, 20);

        /**
         * At this point if the storage volume is null, we need to create a storage volume for the compute member.
         * To do this we need to create a directory in storage member and then mount that volume to this compute member.
         */
        if(!$storageVolume) {
            $this->setProgress(20, 'Creating storage volume for the compute member');
            $storageVolume = StorageMemberXenService::createStorageVolume($computeMember, $this->storagePool);
        }

        $provisioningDriver = app(VirtualMachineManager::class)->getAdapter($this->virtualMachine);

        if ($provisioningDriver instanceof ProvisioningCapableInterface) {
            $this->setProgress(30, 'Importing virtual machine image');
            $uuid = $provisioningDriver->importFromImage(
                $this->virtualMachine,
                $computeMember,
                $this->repository,
                $storageVolume,
                $this->image,
                false
            );
        } else {
            $this->setProgress(15, 'Mounting repository to compute member');
            ComputeMemberXenService::mountVmRepository($computeMember, $this->repository);

            $this->setProgress(30, 'Importing virtual machine image');
            $uuid = ComputeMemberXenService::importVirtualMachine($computeMember, $storageVolume, $this->image, $this->virtualMachine);
        }

        $this->setProgress(40, 'Updating virtual machine parameters');

        $vmParams = $provisioningDriver instanceof ProvisioningCapableInterface
            ? $provisioningDriver->getVmParametersByRef($computeMember, $uuid)
            : VirtualMachinesXenService::getVmParametersByUuid($computeMember, $uuid);

        $this->virtualMachine->update([
            'hypervisor_uuid'           =>  $vmParams['uuid'],
            'hypervisor_data'           =>  $vmParams,
            'iaas_compute_member_id'    =>  $computeMember->id,
            'state'                     =>  $vmParams['power-state'],
            'os'        =>  $this->image->os,
            'distro'    =>  $this->image->distro,
            'version'   =>  $this->image->version,
            'is_draft'  =>  false,
            'status'    =>  'halted'
        ]);

        Events::listen('imported:NextDeveloper\IAAS\VirtualMachines', $this->virtualMachine);
        Events::listen('imported-virtual-machine:NextDeveloper\IAAS\ComputePools', $this->computePool);
        Events::listen('imported-virtual-machine:NextDeveloper\IAAS\ComputeMembers', $computeMember);

        $this->setProgress(50, 'Updating virtual machine CPU');

        if ($provisioningDriver instanceof ResizeCapableInterface) {
            $provisioningDriver->resize($this->virtualMachine, $this->virtualMachine->cpu, $this->virtualMachine->ram);
        } else {
            VirtualMachinesXenService::setCPUCore($this->virtualMachine, $this->virtualMachine->cpu);

            $this->setProgress(60, 'Updating virtual machine RAM');
            VirtualMachinesXenService::setRam($this->virtualMachine, $this->virtualMachine->ram);
        }

        $this->setProgress(70, 'Updating virtual machine RAM');
        $disks = VirtualMachinesXenService::getVmDisks($this->virtualMachine);

        $this->setProgress(80, 'Updating virtual machine disks/cdroms');

        //  Not routed through VirtualMachineManager: this populates brand-new
        //  VirtualDiskImages rows from scratch for a freshly-imported VM (no existing DB
        //  config to diff against), which doesn't match reconcileDiskConfiguration()'s
        //  "diff hypervisor state against existing DB config" shape - see
        //  docs/hypervisor-driver-architecture.md.
        foreach ($disks as $disk) {
            $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($disk['vdi-uuid'], $computeMember);
            $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($disk['uuid'], $computeMember);

            //  Storage volume is looked up by the VDI's own storage-repository uuid
            //  (sr-uuid), not the VBD's uuid - those are unrelated identifiers, and
            //  matching on the wrong one meant this lookup could never find a real row.
            $diskVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $diskParams['sr-uuid'])
                ->first();

            $data = [
                'name'                      =>  $vbdParams['type'] !== 'CD' ? 'Disk of: ' . $this->virtualMachine->name : 'CDROM',
                'size'                      =>  $vbdParams['type'] !== 'CD' ? $diskParams['virtual-size'] : 0,
                'physical_utilisation'      =>  $vbdParams['type'] !== 'CD' ? $diskParams['physical-utilisation'] : 0,
                'iaas_storage_volume_id'    =>  $vbdParams['type'] !== 'CD' ? $diskVolume->iaas_storage_volume_id : null,
                'iaas_virtual_machine_id'   =>  $this->virtualMachine->id,
                'device_number'             =>  $vbdParams['userdevice'],
                'is_cdrom'                  =>  $vbdParams['type'] === 'CD',
                'hypervisor_uuid'       =>  $vbdParams['vdi-uuid'],
                'hypervisor_data'       =>  $disk,
                'iam_account_id'        =>  $this->virtualMachine->iam_account_id,
                'iam_user_id'           =>  $this->virtualMachine->iam_user_id,
                'is_draft'              =>  false,
                'vbd_hypervisor_data'   =>  $vbdParams,
                'vbd_hypervisor_uuid'   =>  $vbdParams['uuid']
            ];

            VirtualDiskImages::create($data);
        }

        $this->setProgress(90, 'Unmounting repository from compute member');

        if ($provisioningDriver instanceof ProvisioningCapableInterface) {
            $provisioningDriver->unmountRepository($computeMember, $this->repository);
        } else {
            ComputeMemberXenService::unmountVmRepository($computeMember, $this->repository);
        }

        $this->setProgress(100, 'Virtual machine imported');
    }
}
