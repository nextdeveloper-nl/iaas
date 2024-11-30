<?php

namespace NextDeveloper\IAAS\Services;

use App\Helpers\Http\ResponseHelper;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Exceptions\NotFoundException;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotUpdateResourcesException;
use NextDeveloper\IAAS\Helpers\ResourceCalculationHelper;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for VirtualMachines
 *
 * Class VirtualMachinesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachinesService extends AbstractVirtualMachinesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create(array $data)
    {
        //  Getting the actual amount of ram
        $data['ram']    =   ResourceCalculationHelper::getActualRam($data['ram']);

        //  Asking the appropriate number of CPU per ram.
        $data['cpu']    =   ResourceCalculationHelper::getCpuPerRam(
            ram: $data['ram'],
            //  We will be adding this parameter later to get the actual CPU size for compute pool
            cp: null
        );

        //  Finding and attaching cloud node id
        if(array_key_exists('iaas_compute_pool_id', $data)) {
            $computePool = null;

            if(Str::isUuid($data['iaas_compute_pool_id'])) {
                $computePool = ComputePools::where('uuid', $data['iaas_compute_pool_id'])->first();
            } else {
                $computePool = ComputePools::where('id', $data['iaas_compute_pool_id'])->first();
            }

            $cloudNode = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $computePool->iaas_cloud_node_id)
                ->first();

            $data['iaas_cloud_node_id'] = $cloudNode->id;
        }

        //  So with this setup, we set our maximum available ram to 2048 GB
        $data['ram'] = ResourceCalculationHelper::getRamInMb($data['ram']);

        return parent::create($data);
    }

    public static function getComputeMember(VirtualMachines $vm) : ?ComputeMembers
    {
        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();
    }

    public static function getComputePool(VirtualMachines $vm) : ?ComputePools
    {
        return ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_pool_id)
            ->first();
    }

    public static function getCloudPool($vm) {
        $computePool = self::getComputePool($vm);

        return CloudNodes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $computePool->iaas_cloud_node_id)
            ->first();
    }

    public static function getPasswordById($id)
    {
        $vm = VirtualMachines::where('uuid', $id)->first();

        try {
            $password = decrypt($vm->password);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            if($e->getMessage() == 'The payload is invalid.') {
                Log::error(__METHOD__ . ' | We got the payload is invalid error. Maybe the password is not ' .
                    'encrpyted for the customer. That is why I am returning the raw password');

                return ResponseHelper::status($vm->password);
            }
        }

        return ResponseHelper::status($password);
    }

    public static function update($id, array $data)
    {
        $vm = VirtualMachines::findByUuid($id);
        $cp = self::getComputePool($vm);

        $triggerVdiUpdate = false;
        $triggerRamUpdate = false;

        if(!$vm) {
            throw new NotFoundException('Cannot find the virtual machine you are trying to update. This ' .
                'can be because of multiple reasons but most probably vm is not there. Therefore it can be a wise ' .
                'decision to run a manual health check for this VM.');
        }

        if($vm->ram != $data['ram']) {
            if($vm->hypervisor_uuid) {
                if($vm->status != 'halted')
                    throw new CannotUpdateResourcesException('Unfortunately we cannot update the resources ' .
                        'of your virtual machine because your virtual machine is still running. Please shutdown your ' .
                        'server and try updating the resources again.');
            }

            $canUpdateRam = self::canUpdateRam($vm, $data['ram']);

            if(!$canUpdateRam) {
                $availableRamSizes = ResourceCalculationHelper::getAvailableRamSizes($cp);

                throw new CannotUpdateResourcesException('We cannot update the ram and cpu because the ram ' .
                    'that you are asking to increase is either beyond our available ram or the amount of ram is not ' .
                    'in the list of available ram amounts. To fix this problem please check if the ram size is ' .
                    'within this list: ' . implode(' GB, ', $availableRamSizes) . ' GB');
            }

            /*  If we can update the ram, we should also take a look at the disk. Because if the server is in STAR
            *   design we can update but if we are in ONE design we should check if we can update the disk also
            */
            $shouldUpdateDisk = self::shouldUpdateDiskWithRam($vm);

            //  If we should update then I am updating the disk
            //  Also if we should update the disk this means that the pool is ONE
            if($shouldUpdateDisk) {
                //  Since this is Leo One type or pool, we cannot allow to reduce resources.
                if(ResourceCalculationHelper::getRamInMb($data['ram']) < $vm->ram) {
                    throw new CannotUpdateResourcesException('We cannot update resources of this server,' .
                        ' because the server is in Leo ONE pool where cpu, ram and disk resources are aligned with a ' .
                        'certain ratio. The problem here is that we cannot reduce the size of the disk, therefore ' .
                        'we cannot reduce the size of CPU and RAM. We are very sorry about this issue.');
                }

                //  @leo-pool ONE
                //  If we came to this point this means that we have enough resources in the resource pool.
                $cm = self::getComputeMember($vm);

                //  If we have a compute member, this means that we should be taking a look at the CM resources.
                //  If CM also has resource then everything is fine, we can move on.
                if($cm) {
                    //  Since the ram and disk are correlated in this design, we don't need to check for disk again.
                    if(!ComputeMembersService::hasRamResource($cm, $data['ram'])) {
                        throw new CannotUpdateResourcesException('We cannot update your virtual machines ' .
                            'resources because on the host that you are using there is not enough resource. You ' .
                            'should create a new server or you should enable migrate server option while asking for ' .
                            'resize. But you should be aware that when you are migrating your server, you will have ' .
                            'some downtime. Also you may not have the same hardware and your bios may change.');
                    }
                }

                //  This means that we have done all the checks and we are good to go for VDI update
                $triggerVdiUpdate = true;
            }

            if(!$shouldUpdateDisk) {
                //  @leo-pool STAR
                $canUpdateDisk = self::canUpdateDisk(
                    vm: $vm,
                    toDisk: ResourceCalculationHelper::getDiskSizeAgainstRam(
                        cp: self::getComputePool($vm),
                        ram: $data['ram']
                    )
                );

                $availableDiskSizes = ResourceCalculationHelper::getAvailableDiskSizes(
                    cp: self::getComputePool($vm),
                    minSize: ResourceCalculationHelper::getDiskSizeAgainstRam(
                        cp: self::getComputePool($vm),
                        ram: $data['ram']
                    )
                );

                if(!$canUpdateDisk) {
                    throw new CannotUpdateResourcesException('We cannot update the disk. The disk you are ' .
                        'requesting either is not available or you cannot take that much. Try to ask for these ' .
                        'amounts; ' . implode(' GB, ', $availableDiskSizes) . ' GB. Or you may have requested ' .
                        'either ram to change or disk to change. If the compute pool is in one mode and you asked for ' .
                        'ram to change, then we should also change the disk.');
                }
            }

            $triggerRamUpdate = true;
        }

        if($triggerVdiUpdate) {
            $vdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $vm->id)
                ->where('device_number', 0)
                ->first();

            VirtualDiskImagesService::update($vdi->id, [
                'size'  =>  ResourceCalculationHelper::getDiskSizeAgainstRam($cp)
            ]);
        } else {
            $data['status'] = 'pending-update';
        }

        if(!$triggerRamUpdate) {
            unset($data['cpu']);
            unset($data['ram']);
        } else {
            $data['cpu']    = ResourceCalculationHelper::getCpuPerRam($data['ram'], $cp);
            $data['ram']    = ResourceCalculationHelper::getRamInMb($data['ram']);
            $data['status'] = 'pending-update';
        }

        $updatedVm = parent::update($id, $data);

        if($vm->hypervisor_uuid) {
            dispatch(new Commit($vm));
        }

        return $updatedVm;
    }

    /**
     * If the compute pool design is in One design then we should update the disk, if it is in star design, then we
     * dont need to update the disk.
     *
     * @param VirtualMachines $vm
     * @return bool
     */
    public static function shouldUpdateDiskWithRam(VirtualMachines $vm)
    {
        $cp = self::getComputePool($vm);

        return $cp->pool_type == 'one';
    }

    public static function canUpdateDisk(VirtualMachines $vm, $toDisk) {
        //  At the moment we are not letting the customer make live resource update. That is why we are checking if
        //  the VM is shutdown or not.
        if(!($vm->status == 'draft' || $vm->status == 'halted'))
            return false;

        if($vm->iaas_compute_member_id) {
            $availableDiskSizes = ResourceCalculationHelper::getAvailableDiskSizesForComputeMember(
                cm: self::getComputeMember($vm)
            );

            return $availableDiskSizes;
        }

        return ResourceCalculationHelper::getAvailableDiskSizes(
            cp: self::getComputePool($vm)
        );
    }

    /**
     * Here we are checking if we can update the amount of ram to the given ram, according to the vm resource
     * configuration given by the administrator of this system.
     *
     * @param VirtualMachines $vm
     * @param $toRam
     * @return void
     */
    public static function canUpdateRam(VirtualMachines $vm, $toRam) {
        //  At the moment we are not letting the customer make live resource update. That is why we are checking if
        //  the VM is shutdown or not.
        if(!($vm->status == 'draft' || $vm->status == 'halted'))
            return false;

        //  This means that we need to check the ram because the user requested another ram
        $availableRamSizes = ResourceCalculationHelper::getAvailableRamSizes(
            cp: self::getComputePool($vm)
        );

        if(!in_array($toRam, $availableRamSizes)) {
            return false;
        }

        return true;
    }
}
