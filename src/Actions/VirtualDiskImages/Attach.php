<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;

/**
 * This action attaches the virtual disk image to the virtual machine on the hypervisor.
 */
class Attach extends AbstractAction
{
    public const EVENTS = [
        'attaching:NextDeveloper\IAAS\VirtualDiskImages',
        'attached:NextDeveloper\IAAS\VirtualDiskImages',
        'attach-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $diskImage, $params = null, $previous = null)
    {
        $this->model = $diskImage;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual disk image attach');

        Events::fire('attaching:NextDeveloper\IAAS\VirtualDiskImages', $this->model);

        $computePool = VirtualDiskImagesService::getComputePool($this->model);
        $driver = $computePool ? app(VirtualMachineManager::class)->getAdapterForComputePool($computePool) : null;

        if (!$driver instanceof DiskCapableInterface) {
            Events::fire('attach-failed:NextDeveloper\IAAS\VirtualDiskImages', $this->model);
            $this->setFinishedWithError('No driver capable of attaching disks is registered for this compute pool.');
            return;
        }

        $this->model = $driver->attachDisk($this->model);

        Events::fire('attached:NextDeveloper\IAAS\VirtualDiskImages', $this->model);

        $this->setProgress(100, 'Virtual disk image attached');
    }
}
