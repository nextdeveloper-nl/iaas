<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
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
        $this->setProgress(0, 'Delete virtual machine started');
        Events::fire('deleting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            CommentsService::createSystemComment('This VM seems to be lost, that is why we are not continuing the deletion process.', $this->model);
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
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
            VirtualMachinesXenService::forceShutdown($this->model);
            VirtualMachinesXenService::destroyVm($this->model);

            //VirtualMachinesService::delete($this->model->uuid);

            $vdis = VirtualDiskImages::withoutGlobalScope(\NextDeveloper\IAM\Database\Scopes\AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $this->model->id)
                ->delete();

            //  We also need to delete all the disks that are attached to this VM physically. This will be implemented in the future.

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
