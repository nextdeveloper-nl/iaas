<?php

namespace NextDeveloper\IAAS\Actions\VirtualDiskImages;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
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
            $computeMember = VirtualDiskImagesService::getComputeMember($vdi);

            $driver = $computePool
                ? app(VirtualMachineManager::class)->getAdapterForComputePool($computePool)
                : null;

            if ($driver instanceof DiskCapableInterface && $computeMember && $vdi->hypervisor_uuid) {
                //  The driver's destroyDisk() already detaches first if vbd_hypervisor_data is
                //  set - the hypervisor refuses to destroy a VDI that still has a VBD attached.
                $this->setProgress(50, 'Destroying the disk image on the hypervisor.');

                $driver->destroyDisk($vdi);

                $vdi->updateQuietly([
                    'hypervisor_uuid'  => null,
                    'hypervisor_data'  => null
                ]);
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
}
