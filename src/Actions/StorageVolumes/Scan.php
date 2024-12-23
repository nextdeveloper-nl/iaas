<?php
namespace NextDeveloper\IAAS\Actions\StorageVolumes;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Sync;
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
    }

    private function syncXenServer(ComputeMembers $computeMember)
    {
        $getDisks = ComputeMemberXenService::getListOfDisksOnVolume($computeMember, $this->model);

        foreach ($getDisks as $disk) {
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

            /**
             * Burada sanki hata var, aşağıdaki $disk['uuid'] olmamalı. Tekrar kontrol et.
             */

            $diskVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $diskParams['sr-uuid'])
                ->first();

            $data = [
                'name' => $dbDisk ? $dbDisk->name : $diskParams['name-label'],
                'size' => $diskParams['virtual-size'],
                'physical_utilisation' => $diskParams['physical-utilisation'],
                'iaas_storage_volume_id' => $diskVolume->iaas_storage_volume_id,
                'iaas_storage_pool_id' => $diskVolume->iaas_storage_pool_id,
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
                    //  Here we should sync the VM
                    dd('NO VM HERE');
                }

                $data = array_merge($data, [
                    'iaas_virtual_machine_id' => $vm->id,
                    'device_number' => $vbdParams['userdevice'],
                    'iam_account_id' => $vm ? $vm->iam_account_id : config('leo.current_account_id'),
                    'iam_user_id' => $vm? $vm->iam_user_id : config('leo.current_user_id'),
                    'vbd_hypervisor_uuid' => $vbdParams['uuid'],
                    'vbd_hypervisor_data' => $vbdParams,
                    'created_at' => $vm->created_at
                ]);
            }

            if (!$dbDisk) {
                Log::info(__METHOD__ . ' | creating: ' . $data['hypervisor_uuid']);
                VirtualDiskImages::create($data);
            } else {
                Log::info(__METHOD__ . ' | Updating: ' . $dbDisk->uuid);
                $dbDisk->updateQuietly($data);
            }
        }
    }
}
