<?php
namespace NextDeveloper\IAAS\Actions\ComputePools;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\ProvisioningAlgorithms\ComputeMembers\UtilizeComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes\UtilizeStorageVolumes;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\StorageMemberXenService;
use NextDeveloper\IAM\Database\Models\Users;
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
        'importing:NextDeveloper\IAAS\ComputePools',
        'imported:NextDeveloper\IAAS\ComputePools',
    ];

    public const PARAMS = [
        'iaas_repository_image_id'  =>  'required|exists:iaas_repository_images,uuid',
        'iaas_storage_pool_id'      =>  'required|exists:iaas_storage_pools,uuid',
        'iaas_virtual_machine_id'   =>  'required|exists:iaas_virtual_machine_id',
        'ram'                       =>  'integer',
        'cpu'                       =>  'integer'
    ];

    private $image;

    private $repository;

    private $storagePool;

    private $computePool;

    private $storageVolume;

    private $ram = 0;

    private $cpu = 0;

    public function __construct(ComputePools $pool, $params)
    {
        if(array_key_exists(0, $params))
            $params = $params[0];

        parent::__construct($params);

        $this->model = $pool;
        $this->computePool = $pool;

        $this->image = RepositoryImages::where('uuid', $params['iaas_repository_image_id'])->first();
        $this->storagePool = StoragePools::where('uuid', $params['iaas_storage_pool_id'])->first();

        $this->ram = $this->image->ram;
        $this->cpu = $this->image->cpu;

        if(array_key_exists('ram', $params))
            $this->ram = $params['ram'];

        if(array_key_exists('cpu', $params))
            $this->ram = $params['cpu'];
    }

    public function handle()
    {
        $this->setProgress(0, 'Importing virtual machine started');

        $this->setProgress(5, 'Finding the repository');

        $this->repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->image->iaas_repository_id)
            ->first();

        $this->setProgress(5, 'Finding the best compute member');

        $computeMember = (new UtilizeComputeMembers($this->computePool))->calculate($this->ram, $this->cpu);

        $this->setProgress(10, 'Finding the best storage volume for the compute member');

        $storageVolume = (new UtilizeStorageVolumes($this->storagePool))->calculate($computeMember, 20);

        /**
         * At this point if the storage volume is null, we need to create a storage volume for the compute member.
         * To do this we need to create a directory in storage member and then mount that volume to this compute member.
         */
        if(!$storageVolume) {
            $this->setProgress(13, 'Creating storage volume for the compute member');
            $storageVolume = StorageMemberXenService::createStorageVolume($computeMember, $this->storagePool);
        }

//        $this->setProgress(15, 'Mounting repository to compute member');
//        ComputeMemberXenService::mountVmRepository($computeMember, $this->repository);

        $this->setProgress(20, 'Importing virtual machine image');
        $uuid = ComputeMemberXenService::importVirtualMachine($computeMember, $storageVolume, $this->image);

        $this->syncVirtualMachine($uuid, $this->params);

//        $this->setProgress(90, 'Unmounting repository from compute member');
//        ComputeMemberXenService::unmountVmRepository($computeMember, $this->repository);

        $this->setProgress(100, 'Virtual machine imported');
    }


}
