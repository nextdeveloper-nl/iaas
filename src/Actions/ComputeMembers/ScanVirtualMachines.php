<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;

/**
 * This action will scan compute member and sync all findings
 */
class ScanVirtualMachines extends AbstractAction
{
    public const EVENTS = [
        'scanned:NextDeveloper\IAAS\ComputeMembers'
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        $this->model = $computeMember;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $virtualMachines = ComputeMemberXenService::getListOfVirtualMachines($this->model);

        $vmCount = count($virtualMachines);

        $this->setProgress(10, 'Found ' . $vmCount . ' virtual machines, scanning one by one.');

        $step = $vmCount / 40;

        for($i = 0; $i < $vmCount; $i++) {
            $vm = $virtualMachines[$i];

            $this->setProgress(10 + ceil($i * $step), 'Scanning virtual machine number: ' . $i);
            Log::info('[ScanVirtualMachines] Scanning virtual machine number: ' . $i . ' [' . $vm['name-label'] . '] / [' . $vm['uuid'] . ']');

            $vmInfo = ComputeMemberXenService::getVirtualMachineByUuid($this->model, $vm['uuid']);

            if(is_array($vmInfo) && array_key_exists('error', $vmInfo)) {
                Log::error('[ScanVirtualMachines] Error while scanning virtual machine: ' . $vm['uuid']);
                Log::error($vmInfo);
                continue;
            }

            if(is_array($vmInfo)) {
                $vmInfo = $vmInfo[0];
            }

            Log::info('[ScanVirtualMachines] Checking if the VM exists in database: ' . $vm['uuid']);

            $dbVm = VirtualMachines::withoutGlobalScopes()->where('hypervisor_uuid', $vm['uuid'])->first();

            $computePool = ComputePools::withoutGlobalScopes()->where('id', $this->model->iaas_compute_pool_id)->first();
            $cloudNode  = CloudNodes::withoutGlobalScopes()->where('id', $computePool->iaas_cloud_node_id)->first();

            if($dbVm) {
                Log::info('[ScanVirtualMachines] VM exists in database: ' . $vm['uuid']);
                $dbVm->update([
                    'name'          =>  $vmInfo['name-label'],
                    'domain_type'   =>  $vmInfo['hvm'] == 'false' ? 'pv' : 'hvm',
                    'cpu'           =>  $vmInfo['VCPUs-max'],
                    'ram'           =>  $vmInfo['memory-static-max'] / 1024 / 1024, //  this comes in bytes, conterting to MB,
                    'status'        =>  $vmInfo['power-state'],
                    'available_operations'  =>  $vmInfo['allowed-operations'],
                    'current_operations'    =>  $vmInfo['current-operations'],
                    'blocked_operations'    =>  $vmInfo['blocked-operations'],
                    'hypervisor_uuid'       =>  $vm['uuid'],
                    'hypervisor_data'       =>  $vmInfo,
                    'is_draft'              =>  false,
                    'iaas_compute_member_id'    =>  $this->model->id,
                    'iaas_cloud_node_id'        =>  $cloudNode->id,
                    'iaas_compute_pool_id'      =>  $computePool->id
                ]);
                $dbVm->save();
            } else {
                Log::info('[ScanVirtualMachines] VM does not exist in database: ' . $vm['uuid']);

                VirtualMachines::create([
                    'name'          =>  $vmInfo['name-label'],
                    'domain_type'   =>  $vmInfo['hvm'] == 'false' ? 'pv' : 'hvm',
                    'cpu'           =>  $vmInfo['VCPUs-max'],
                    'ram'           =>  $vmInfo['memory-static-max'] / 1024 / 1024, //  this comes in bytes, conterting to MB,
                    'status'        =>  $vmInfo['power-state'],
                    'available_operations'  =>  $vmInfo['allowed-operations'],
                    'current_operations'    =>  $vmInfo['current-operations'],
                    'blocked_operations'    =>  $vmInfo['blocked-operations'],
                    'hypervisor_uuid'       =>  $vm['uuid'],
                    'hypervisor_data'       =>  $vmInfo,
                    'is_draft'              =>  false,
                    'iaas_compute_member_id'    =>  $this->model->id,
                    'iaas_cloud_node_id'        =>  $cloudNode->id,
                    'iaas_compute_pool_id'      =>  $computePool->id,
                    'iam_account_id'            =>  $this->model->iam_account_id,
                    'iam_user_id'               =>  $this->model->iam_user_id
                ]);
            }
        }

        /**
         * HERE WE WILL SCAN NETWORK CARDS AND DISKS TOO
         */

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member scanned and synced');
    }
}
