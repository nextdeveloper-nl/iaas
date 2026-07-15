<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;

/**
 * This action attaches the virtual disk image to the virtual machine
 */
class Resize extends AbstractAction
{
    public const EVENTS = [
        'resizing:NextDeveloper\IAAS\VirtualDiskImages',
        'resized:NextDeveloper\IAAS\VirtualDiskImages',
        'resize-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $diskImage, $params = null, $previous = null)
    {
        $this->model = $diskImage;

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Virtual disk image resize starting.');

        $this->resizeDisk();

        $this->setProgress(100, 'Virtual disk image is resized.');
    }

    private function resizeDisk()
    {
        $computePool = VirtualDiskImagesService::getComputePool($this->model);
        $driver = $computePool ? app(VirtualMachineManager::class)->getAdapterForComputePool($computePool) : null;

        if ($driver instanceof DiskCapableInterface) {
            $driver->resizeDisk($this->model, $this->model->size);

            dispatch(new Sync($this->model));
        }
    }
}
