<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use App\Services\IAAS\VirtualMachineServices;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * This action converts the virtual machine into a template
 */
class Snapshot extends AbstractAction
{
    public const EVENTS = [
        'taking-snapshot:NextDeveloper\IAAS\VirtualMachines',
        'snapshot-taken:NextDeveloper\IAAS\VirtualMachines',
        'snapshot-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        $this->setProgress(10, 'Taking the snapshot of the virtual machine');

        $snapshot = VirtualMachinesXenService::takeSnapshot($this->model);

        if($snapshot['error']) {
            //  There is an error
            dd($snapshot);
        }

        $uuid = $snapshot['output'];

        $snapshot = VirtualMachinesService::create([
            'name'  =>  'Snapshot of ' . $this->model->name,
            'hypervisor_uuid'   =>  $uuid,
            'is_snapshot'   =>  true,
            'is_draft'  =>  false,
            'os'    =>  $this->model->os,
            'distro'    =>  $this->model->distro,
            'version'   =>  $this->model->version,
            'status'    =>  'halted',
            'cpu'   =>  $this->model->cpu,
            'ram'   =>  $this->model->ram,
            'auto_backup_interval'  =>  'none',
            'auto_backup_time'  =>  'none',
            'iaas_compute_pool_id'  =>  $this->model->iaas_compute_pool_id,
            'iaas_compute_member_id'    =>  $this->model->iaas_compute_member_id,
            'iaas_cloud_node_id'  =>  $this->model->iaas_cloud_node_id
        ]);

        VirtualMachinesXenService::fixName($snapshot);

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
