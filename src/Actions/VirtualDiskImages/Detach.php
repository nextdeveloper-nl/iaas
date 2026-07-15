<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action detaches the virtual disk image from the virtual machine
 */
class Detach extends AbstractAction
{
    public const EVENTS = [
        'detaching:NextDeveloper\IAAS\VirtualDiskImages',
        'detached:NextDeveloper\IAAS\VirtualDiskImages',
        'detach-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $diskImage, $params = null, $previous = null)
    {
        $this->model = $diskImage;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual disk image detach');

        $vdi = $this->model;

        Events::fire('detaching:NextDeveloper\IAAS\VirtualDiskImages', $vdi);

        if (!$vdi->vbd_hypervisor_uuid) {
            //  Disk was never attached on the hypervisor (eg. still in draft state).
            Events::fire('detached:NextDeveloper\IAAS\VirtualDiskImages', $vdi);
            $this->setFinished('Disk image was not attached, nothing to detach.');
            return;
        }

        $vm = VirtualDiskImagesService::getVirtualMachine($vdi);

        if (!$vm || $vm->hypervisor_data == null) {
            Log::info('[VirtualDiskImages@Detach] Seems like VM is still in draft state or is missing, ' .
                'so there is nothing to detach on the hypervisor.');

            $vdi->updateQuietly([
                'vbd_hypervisor_uuid'  => null,
                'vbd_hypervisor_data'  => null
            ]);

            Events::fire('detached:NextDeveloper\IAAS\VirtualDiskImages', $vdi);
            $this->setFinished('Disk image detached.');
            return;
        }

        $this->setProgress(50, 'Detaching the disk image from the hypervisor.');

        $computePool = VirtualMachinesService::getComputePool($vm);
        $driver = $computePool ? app(VirtualMachineManager::class)->getAdapterForComputePool($computePool) : null;

        if ($driver instanceof DiskCapableInterface) {
            $driver->detachDisk($vdi);
        } else {
            VirtualDiskImageXenService::detach($vdi);
        }

        Events::fire('detached:NextDeveloper\IAAS\VirtualDiskImages', $vdi);

        $this->setFinished('Disk image detached.');
    }
}
