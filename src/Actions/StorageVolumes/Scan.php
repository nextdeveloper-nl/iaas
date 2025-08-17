<?php
namespace NextDeveloper\IAAS\Actions\StorageVolumes;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualDiskImageXenService;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\StorageVolumes',
        'scanned:NextDeveloper\IAAS\StorageVolumes',
        'scan-failed:NextDeveloper\IAAS\StorageVolumes'
    ];

    public function __construct(StorageVolumes $volume, $params = null, $previous = null)
    {
        $this->model = $volume;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Scanning storage volume started: ' . $this->model->name);

        $list = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_storage_volume_id', $this->model->id)
            ->get();

        foreach ($list as $item) {
            $cm = ComputeMembersService::getById($item->iaas_compute_member_id);

            switch ($cm->hypervisor_model) {
                case 'XenServer 8.2':
                    $this->syncXenServer($cm);
            }
        }

        $this->setFinished('Scanning storage volume finished: ' . $this->model->name);
    }

    private function syncXenServer(ComputeMembers $computeMember)
    {
        $getDisks = ComputeMemberXenService::getListOfDisksOnVolume($computeMember, $this->model);

        foreach ($getDisks as $disk) {
            if(!array_key_exists('uuid', $disk))
                continue;

            Log::info(__METHOD__ . ' | Syncing disk: ' . $disk['uuid']);

            $dbDisk = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $disk['uuid'])
                ->first();

            if ($dbDisk) {
                Log::info(__METHOD__ . ' | Disk is in DB syncing: ' . $disk['uuid']);
            }

            Log::info(__METHOD__ . ' | Creating or updating object for disk: ' . $disk['uuid']);

            $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($disk['uuid'], $computeMember);

            $vbdParams = null;

            if ($diskParams['vbd-uuids']) {
                $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($diskParams['vbd-uuids'], $computeMember);
            }

            $volume = StorageVolumesService::getVolumeByUuid($diskParams['sr-uuid']);

            if (!$volume) {
                Log::warning(__METHOD__ . ' | Disk does not have storage volume in DB. We should start ' .
                    'storage volume sync for this compute member');

                ComputeMemberXenService::updateStorageVolumes($computeMember);
            }

            $data = [
                'name' => $dbDisk ? $dbDisk->name : $diskParams['name-label'],
                'size' => $diskParams['virtual-size'],
                'physical_utilisation' => $diskParams['physical-utilisation'],
                'iaas_storage_volume_id' => $volume->id,
                'iaas_storage_pool_id' => $volume->iaas_storage_pool_id,
                'is_cdrom' => false,
                'hypervisor_uuid' => $diskParams['uuid'],
                'hypervisor_data' => $disk,
                //  here we are adding default user for the disks. If we can find the vm, then we will change it.
                'iam_account_id' => $dbDisk ? $dbDisk->iam_account_id : config('leo.current_account_id'),
                'iam_user_id' => $dbDisk ? $dbDisk->iam_user_id : config('leo.current_user_id'),
                'is_draft' => false,
            ];

            if ($vbdParams) {
                $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $vbdParams['vm-uuid'])
                    ->first();

                if (!$vm) {
                    Log::warning(__METHOD__ . ' | We cannot find the VM with uuid: ' . $vbdParams['vm-uuid']);
                }

                $data = array_merge($data, [
                    'iaas_virtual_machine_id' => $vm ? $vm->id : null,
                    'device_number' => $vm ? $vbdParams['userdevice'] : null,
                    'iam_account_id' => $vm ? $vm->iam_account_id : config('leo.current_account_id'),
                    'iam_user_id' => $vm? $vm->iam_user_id : config('leo.current_user_id'),
                    'vbd_hypervisor_uuid' => $vm ? $vbdParams['uuid'] : null,
                    'vbd_hypervisor_data' => $vm ? $vbdParams : null,
                ]);

                if($dbDisk)
                    $data['created_at'] = $vm ? $vm->created_at : $dbDisk->created_at;
                else
                    $data['created_at'] = $vm ? $vm->created_at : now();
            }

            if (!$dbDisk) {
                Log::info(__METHOD__ . ' | creating: ' . $data['hypervisor_uuid']);
                VirtualDiskImages::create($data);
            } else {
                Log::info(__METHOD__ . ' | Updating: ' . $dbDisk->uuid);
                Log::info(__METHOD__ . ' | Updating with data: ' . json_encode($data));
                $dbDisk->updateQuietly($data);
            }
        }

        $volumeInfo = ComputeMemberXenService::getStorageVolumeInformationByHypervisorUuid($computeMember, $this->model->hypervisor_uuid);

        $this->model->update([
            'total_hdd'         =>  ceil($volumeInfo['physical-size'] / 1000 / 1000 / 1000),
            'used_hdd'          =>  ceil($volumeInfo['physical-utilisation'] / 1000 / 1000 / 1000),
            'virtual_allocation' =>  ceil($volumeInfo['virtual-allocation'] / 1000 / 1000 / 1000),
        ]);
    }
}
