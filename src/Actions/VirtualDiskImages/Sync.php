<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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

        //  Here later we need to take VBD and update those parameters also. At the moment it is not very urgent and/or important

        $data = [
            'size' => $diskParams['virtual-size'],
            'physical_utilisation' => $diskParams['physical-utilisation'],
            'hypervisor_data' => $diskParams,
            'is_draft' => false,
        ];

        $this->model->updateQuietly($data);
    }
}
