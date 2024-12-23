<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;

/**
 * This action attaches the virtual disk image to the virtual machine
 */
class Sync extends AbstractAction
{
    public const EVENTS = [
        'syncing:NextDeveloper\IAAS\VirtualDiskImages',
        'synced:NextDeveloper\IAAS\VirtualDiskImages',
        'sync-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $vdi, $params = null, $previous = null)
    {
        $this->model = $vdi;

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual disk image sync');
        Events::fire(
            eventName: 'syncing:NextDeveloper\IAAS\VirtualDiskImages',
            model: $this->model
        );

        try {
            $computePool = VirtualDiskImagesService::getComputePool($this->model);

            switch ($computePool->virtualization) {
                case 'xenserver-8.2':
                    $this->syncXenDisk();
                    break;
            }
        } catch (\Exception $e) {
            Events::fire(
                eventName: 'sync-failed:NextDeveloper\IAAS\VirtualDiskImages',
                model: $this->model
            );
        }


        Events::fire(
            eventName: 'synced:NextDeveloper\IAAS\VirtualDiskImages',
            model: $this->model
        );
        $this->setProgress(100, 'Virtual disk image synced');
    }

    public function syncXenDisk()
    {
        $computeMember = VirtualDiskImagesService::getComputeMember($this->model);

        $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($this->model['hypervisor_uuid'], $computeMember);
        $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($this->model['uuid'], $computeMember);

        $volume = StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);

        if(!$volume) {
            Log::warning(__METHOD__ . ' | Disk does not have storage volume in DB. We should start ' .
                'storage volume sync for this compute member');

            ComputeMemberXenService::updateStorageVolumes($computeMember);
        }

        $data = [
            'size' => $diskParams['virtual-size'],
            'physical_utilisation' => $diskParams['physical-utilisation'],
            'hypervisor_data' => $diskParams,
            'is_draft' => false,
            'vbd_hypervisor_data'   =>  $vbdParams,
            'vbd_hypervisor_uuid'   =>  $vbdParams['uuid'],
            'iaas_storage_volume_id' =>  $volume->id,
            'iaas_storage_pool_id'  =>  $volume->iaas_storage_pool_id,
            //'device_number' =>  $vbdParams ? 0 : null,
        ];

        $this->model->updateQuietly($data);
    }
}
