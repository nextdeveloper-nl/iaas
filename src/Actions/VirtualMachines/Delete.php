<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Destroy as DestroyVirtualDiskImage;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action converts the virtual machine into a template
 */
class Delete extends AbstractAction
{
    public const EVENTS = [
        'deleting:NextDeveloper\IAAS\VirtualMachines',
        'deleted:NextDeveloper\IAAS\VirtualMachines',
        'delete-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm, array $options = [])
    {
        $this->queue = 'iaas';

        $this->model = $vm;

        parent::__construct();
    }

    public function handle()
    {
        //  $this->model->id feeds every where('iaas_virtual_machine_id', ...) query below (VDIs, VIFs,
        //  IP addresses). Laravel's query builder silently rewrites where($col, null) into whereNull($col),
        //  so a null id here would match and destroy every orphaned/unattached record of that type across
        //  the whole database instead of matching nothing. Refuse to proceed rather than risk that.
        if (empty($this->model->id)) {
            Log::error(__METHOD__ . ' | Refusing to delete: virtual machine model has no id.');
            Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->setProgress(0, 'Delete virtual machine started');
        Events::fire('deleting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        //  A VM the hypervisor no longer reports (is_lost) has nothing real to stop or
        //  destroy there - that's exactly the case a customer most needs to be able to
        //  delete, not one where deletion should be blocked. Skip hypervisor-side cleanup
        //  for it (see $isDeployed below) and go straight to removing its own records.
        if($this->model->is_lost) {
            CommentsService::createSystemComment('This VM is marked as lost - skipping hypervisor cleanup and removing its records directly.', $this->model);
        }

//        if($this->model->deleted_at != null) {
//            $this->setFinished('I cannot complete this process because the VM is already deleted');
//            Events::fire('deleted:NextDeveloper\IAAS\VirtualMachines', $this->model);
//            return;
//        }

        if($this->model->is_locked) {
            CommentsService::createSystemComment('Cannot delete the virtual machine because it is locked.', $this->model);
            $this->setFinished('I cannot complete this process because the VM is locked');
            Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        Events::fire('deleting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        try {
            //  A VM that was never deployed (draft, no compute member/hypervisor_uuid) has nothing
            //  to shut down or destroy on a hypervisor - attempting it throws (null compute member)
            //  and aborts the whole delete before we ever reach $this->model->delete() below. Same
            //  reasoning for is_lost: the hypervisor already doesn't know about this VM, so there's
            //  nothing real to stop/destroy there either.
            $isDeployed = !$this->model->is_lost
                && $this->model->iaas_compute_member_id
                && $this->model->hypervisor_uuid;

            if ($isDeployed) {
                $manager = app(VirtualMachineManager::class);

                $this->model = $manager->stop($this->model, true);
                $manager->delete($this->model);
            }

            //VirtualMachinesService::delete($this->model->uuid);

            //  Only disks currently attached to this VM should go with it. A customer can Detach a real
            //  disk and keep it (Detach only clears vbd_hypervisor_uuid, not iaas_virtual_machine_id, so
            //  it would otherwise still show up here). is_draft disks were never synced to a hypervisor
            //  (Sync sets is_draft=false once real) so they only exist as this VM's own intended disk and
            //  should still be cleaned up with it.
            $vdis = VirtualDiskImages::withoutGlobalScope(\NextDeveloper\IAM\Database\Scopes\AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $this->model->id)
                ->where(function ($query) {
                    $query->whereNotNull('vbd_hypervisor_uuid')
                        ->orWhere('is_draft', true);
                })
                ->get();

            foreach ($vdis as $vdi) {
                //  Best-effort: destroy the disk on the hypervisor first (handles detach-if-attached
                //  and draft/undeployed disks internally), then remove the DB record either way so a
                //  hypervisor failure doesn't block the rest of the VM delete.
                (new DestroyVirtualDiskImage($vdi))->handle();

                $vdi->delete();
            }

            $vifs = \NextDeveloper\IAAS\Database\Models\VirtualNetworkCards::withoutGlobalScope(\NextDeveloper\IAM\Database\Scopes\AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $this->model->id)
                ->get();

            foreach ($vifs as $if) {
                $ips = IpAddresses::withoutGlobalScope(\NextDeveloper\IAM\Database\Scopes\AuthorizationScope::class)
                    ->where('iaas_virtual_network_card_id', $if->id)
                    ->delete();

                //  We need to sync the dhcp service on the network also but this will be implemented in the future.

                $if->delete();
            }

            $this->model->delete();
        } catch (\Exception $e) {
            CommentsService::createSystemComment('We cannot delete the virtual machine. Please consult to your administrator.', $this->model);
            Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        CommentsService::createSystemComment('Virtual machine is deleted.', $this->model);
        Events::fire('deleted:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setProgress(100, 'Virtual machine removed');
    }
}
