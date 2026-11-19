<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
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

        //  leo4#1028: this used to do nothing when the driver could not resize and still report "resized", so the
        //  panel showed the new size while the hypervisor kept the old one. Now every path either resizes or fails
        //  loudly and puts the old size back.
        if (!($driver instanceof DiskCapableInterface) || empty($this->model->hypervisor_uuid)) {
            $this->failResize('The disk cannot be resized on the hypervisor (no resize-capable driver or the disk is not created on the hypervisor yet).');
        }

        try {
            $driver->resizeDisk($this->model, $this->model->size);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' | Resize failed for disk ' . $this->model->uuid . ': ' . $e->getMessage());
            $this->failResize('The hypervisor could not resize the disk.');
        }

        dispatch(new Sync($this->model));
    }

    /**
     * Restores the size the disk had before the request, tells the owner on the VM and stops the action.
     */
    private function failResize(string $reason): void
    {
        $previousSize = $this->params['previous_size'] ?? null;

        if ($previousSize !== null) {
            //  Quiet update: this only corrects the record, it must not trigger another resize.
            $this->model->updateQuietly(['size' => (int) $previousSize]);
        }

        $vm = VirtualDiskImagesService::getVirtualMachine($this->model);

        if ($vm) {
            CommentsService::createSystemComment('Disk resize failed. ' . $reason . ' The disk keeps its previous size.', $vm);
        }

        $this->setFinishedWithError($reason);

        throw new \RuntimeException($reason);
    }
}
