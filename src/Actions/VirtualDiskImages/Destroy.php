<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;

/**
 * This action destroys the virtual disk image on the hypervisor (the inverse of Create).
 */
class Destroy extends AbstractAction
{
    public const EVENTS = [
        'destroying:NextDeveloper\IAAS\VirtualDiskImages',
        'destroyed:NextDeveloper\IAAS\VirtualDiskImages',
        'destroy-failed:NextDeveloper\IAAS\VirtualDiskImages'
    ];

    public function __construct(VirtualDiskImages $diskImage, $params = null, $previous = null)
    {
        $this->model = $diskImage;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual disk image destroy');

        $vdi = $this->model;

        Events::fire('destroying:NextDeveloper\IAAS\VirtualDiskImages', $vdi);

        try {
            $computePool = VirtualDiskImagesService::getComputePool($vdi);

            switch ($computePool?->virtualization) {
                case 'xenserver-8.2':
                    $this->destroyXenDisk();
                    break;
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' | Could not destroy the disk on the hypervisor: ' . $e->getMessage());

            Events::fire('destroy-failed:NextDeveloper\IAAS\VirtualDiskImages', $vdi);
            $this->setFinishedWithError('Could not destroy the disk image on the hypervisor.');
            return;
        }

        Events::fire('destroyed:NextDeveloper\IAAS\VirtualDiskImages', $vdi);

        $this->setFinished('Disk image destroyed.');
    }

    private function destroyXenDisk()
    {
        $vdi = $this->model;
        $computeMember = VirtualDiskImagesService::getComputeMember($vdi);

        if (!$computeMember || !$vdi->hypervisor_uuid) {
            //  Disk was never created on the hypervisor (eg. still draft), nothing to destroy.
            return;
        }

        if ($vdi->vbd_hypervisor_uuid) {
            //  The disk is still plugged into a VM. It needs to be detached first, otherwise the
            //  hypervisor will refuse to destroy a VDI that still has a VBD attached to it.
            VirtualDiskImageXenService::detach($vdi);
        }

        $this->setProgress(50, 'Destroying the disk image on the hypervisor.');

        VirtualDiskImageXenService::destroyDisk($vdi->hypervisor_uuid, $computeMember);

        $vdi->updateQuietly([
            'hypervisor_uuid'  => null,
            'hypervisor_data'  => null
        ]);
    }
}
