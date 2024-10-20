<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $this->scanXenVirtualMachines();

        $this->setProgress(100, 'Compute member scanned and synced');
    }

    public function scanXenVirtualMachines()
    {
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

            /**
             * If the virtual machine exists in the database, we will update it
             */

            if($dbVm) {
                Log::info('[ScanVirtualMachines] VM exists in database: ' . $vm['uuid']);
                $dbVm->update([
                    //'name'          =>  $vmInfo['name-label'],
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

                $dbVm = $dbVm->fresh();

                if(config('iaas.regulations.pci_dss.change_names')) {
                    //$isChanged = ComputeMemberXenService::renameVirtualMachine($this->model, $dbVm);
                    $isChanged = false;

                    if(!$isChanged) {
                        Log::error('[ScanVirtualMachines] Error while renaming virtual machine: ' . $vm['uuid']);
                        StateHelper::setState($this->model, 'host_change_rename_error', 'true');
                    }
                }

            } else {
                Log::info('[ScanVirtualMachines] VM does not exist in database: ' . $vm['uuid']);

                $dbVm = VirtualMachines::create([
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

                if(config('iaas.regulations.pci_dss.change_names')) {
                    //$isChanged = ComputeMemberXenService::renameVirtualMachine($this->model, $dbVm);
                    $isChanged = false;

                    if(!$isChanged) {
                        Log::error('[ScanVirtualMachines] Error while renaming virtual machine: ' . $vm['uuid']);
                        StateHelper::setState($this->model, 'host_change_rename_error', 'true');
                    }
                }
            }

            /**
             * Now we are scanning the VDI of the virtual machine
             */

            $vbds = VirtualMachinesXenService::getVmDisks($dbVm);

            $this->setProgress(10 + ceil($i * $step), 'Found ' . count($vbds) . ' disks. 1 may be a cdrom. Scanning disks of virtual machine number: ' . $i);

            foreach ($vbds as $vbd) {
                //  Sometimes we get null values, we are skipping them (I dont know why)
                if($vbd == [])
                    continue;

                if(array_key_exists('vdi-uuid', $vbd)) {
                    $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($vbd['vdi-uuid'], $this->model);
                }

                $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($vbd['uuid'], $this->model);

                //  We are taking CDROM if the vbd type is CDROM
                if($vbdParams['type'] === 'CD') {
                    $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                        ->where('is_cdrom', true)
                        ->where('iaas_virtual_machine_id', $dbVm->id)
                        ->first();
                } else {
                    $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                        ->where('hypervisor_uuid', $diskParams['uuid'])
                        ->first();
                }

                //  We are taking the volume if the VDI is CDROM
                if($vbdParams['type'] !== 'CD') {
                    $diskVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                        ->where('hypervisor_uuid', $diskParams['sr-uuid'])
                        ->first();

                    if(!$diskVolume) {
                        //  This means that there is a volume but we cannot find it. We need to make sync of this Volume

                    }
                }

                $data = [
                    'name'                      =>  $vbdParams['type'] !== 'CD' ? 'Disk of: ' . $dbVm->name : 'CDROM',
                    'size'                      =>  $vbdParams['type'] !== 'CD' ? $diskParams['virtual-size'] : 0,
                    'physical_utilisation'      =>  $vbdParams['type'] !== 'CD' ? $diskParams['physical-utilisation'] : 0,
                    'iaas_storage_volume_id'    =>  $vbdParams['type'] !== 'CD' ? $diskVolume->iaas_storage_volume_id : null,
                    'iaas_virtual_machine_id'   =>  $dbVm->id,
                    'device_number'             =>  $vbdParams['userdevice'],
                    'is_cdrom'                  =>  $vbdParams['type'] === 'CD',
                    'hypervisor_uuid'       =>  $vbdParams['vdi-uuid'],
                    'hypervisor_data'       =>  $diskParams ?? [],
                    'iam_account_id'        =>  $dbVm->iam_account_id,
                    'iam_user_id'           =>  $dbVm->iam_user_id,
                    'is_draft'              =>  false,
                ];

                if($dbVdi)
                    $dbVdi->update($data);
                else
                    $dbVdi = VirtualDiskImages::create($data);
            }

            /**
             * HERE WE WILL SCAN NETWORK CARDS AND DISKS TOO
             */

            $this->setProgress(10 + ceil($i * $step), 'Scanning network ' .
                'cards of virtual machines: ' . $i);

            $vifs = VirtualMachinesXenService::getVifs($dbVm);

            foreach ($vifs as $vif) {
                if($vif == [])
                    continue;

                $vifParams = VirtualMachinesXenService::getVifParams($dbVm, $vif['uuid']);

                if(array_key_exists(0, $vifParams))
                    $vifParams = $vifParams[0];

                $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $vif['uuid'])
                    ->first();

                $connectedInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                    ->where('network_uuid', $vifParams['network-uuid'])
                    ->first();

                if(!$connectedInterface) {
                    //  Here we will add another trigger to scan all compute member network interfaces
                    StateHelper::setState($this->model, 'needs_scan', true);

                    Log::error('[ScanVirtualMachines] Cannot find the connected ' .
                        'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                        'should be scanned and synced immediately.');

                    continue;
                }

                $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                    ->where('vlan', $connectedInterface->vlan)
                    ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                    ->first();

                if(!$network) {
                    //  Here we need to create another scan and create the related network
                    StateHelper::setState($this->model, 'needs_scan', true);

                    Log::error('[ScanVirtualMachines] Cannot find the connected ' .
                        'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                        'should be scanned and synced immediately.');

                    continue;
                }

                $data = [
                    'name'          =>  'eth' . $vifParams['device'],
                    'device_number' => $vifParams['device'],
                    'mac_addr'      => $vifParams['MAC'],
                    'bandwidth_limit'   => '-1', //$vifParams['qos_algorithm_params']['kbps'],
                    'iaas_network_id'       => $network->id,
                    'hypervisor_uuid'   => $vif['uuid'],
                    'hypervisor_data'   => $vifParams,
                    'iam_account_id'    => $dbVm->iam_account_id,
                    'iam_user_id'       => $dbVm->iam_user_id,
                    'is_draft'          => false,
                    'iaas_virtual_machine_id'   =>  $dbVm->id
                ];

                if($dbVif)
                    $dbVif->update($data);
                else
                    VirtualNetworkCardsService::create($data);
            }
        }

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);
    }
}
