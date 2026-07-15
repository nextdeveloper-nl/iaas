<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;

/**
 * This action creates a VDI from scratch on the hypervisor.
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\VirtualDiskImages',
        'created:NextDeveloper\IAAS\VirtualDiskImages',
        'create-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $diskImage, $params = null, $previous = null)
    {
        $this->model = $diskImage;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual disk image create');

        Events::fire('creating:NextDeveloper\IAAS\VirtualDiskImages', $this->model);

        $computePool = VirtualDiskImagesService::getComputePool($this->model);
        $driver = $computePool ? app(VirtualMachineManager::class)->getAdapterForComputePool($computePool) : null;

        if (!$driver instanceof DiskCapableInterface) {
            Events::fire('create-failed:NextDeveloper\IAAS\VirtualDiskImages', $this->model);
            $this->setFinishedWithError('No driver capable of creating disks is registered for this compute pool.');
            return;
        }

        $this->model = $driver->createDisk($this->model);

        $this->model->update(['is_draft' => false]);

        Events::fire('created:NextDeveloper\IAAS\VirtualDiskImages', $this->model);

        $this->setProgress(100, 'Virtual disk image created');
    }
}
