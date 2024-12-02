<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
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

    public function __construct(VirtualDiskImages $vdi, $params = null, $previous = null)
    {
        $this->model = $vdi;

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

        switch ($computePool->virtualization) {
            case 'xenserver-8.2':
                $this->resizeXenDisks();
                break;
        }
    }

    private function resizeXenDisks()
    {
        $cm = VirtualDiskImagesService::getComputeMember($this->model);

        $result = VirtualDiskImageXenService::resize(
            uuid: $this->model->hypervisor_uuid,
            computeMember: $cm,
            size: $this->model->size
        );

        dispatch(new Sync($this->model));
    }
}
