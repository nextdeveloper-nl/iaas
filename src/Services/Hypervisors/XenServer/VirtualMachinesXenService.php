<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\RepositoriesService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class VirtualMachinesXenService extends AbstractXenService
{
    public static function start(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@start] I am starting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-start uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return $result;
    }

    public static function restart(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@restart] I am restarting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-reboot uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function unpause(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@pause] I am unpausing the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-unpause uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function pause(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@pause] I am pausing the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-pause uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function forceRestart(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@restart] I am restarting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-reboot force=true uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function shutdown(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@shutdown] I am shutting down the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-shutdown uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function forceShutdown(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@shutdown] I am shutting down the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-shutdown force=true uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function takeSnapshot(VirtualMachines $vm, $name = null): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@shutdown] I am taking snapshot of the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        if (!$name)
            $name = 'ss-' . $vm->uuid;

        /*
         * Take the virtual machine name from hypervisor and then use that name in take snapshot command
         */

        $command = 'xe vm-snapshot vm="' . $vm->hypervisor_data['name-label'] . '" new-name-label=' . $name;
        $result = self::performCommand($command, $computeMember);

        return $result;
    }

    public static function convertSnapshotToVm(VirtualMachines $vm, $name = null): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[' . __METHOD__ . '] I am finding snapshots of the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        if (!$name)
            $name = 'Converted ' . $vm->name;

        $command = 'xe snapshot-param-set is-a-template=false uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return $result;
    }

    public static function destroyVm(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::info('[VirtualMachinesXenService@destroyVm] I am deleting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-destroy uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return $result;
    }

    public static function cloneVm(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::info('[VirtualMachinesXenService@cloneVm] I am cloning the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-clone vm="' . $vm->hypervisor_data['name-label'] . '" new-name-label=cloned-' . $vm->uuid;
        $result = self::performCommand($command, $computeMember);

        return $result;
    }

    public static function fixName(VirtualMachines $vm): bool
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::info('[VirtualMachinesXenService@fixName] I am fixing the' .
                ' name of the VM (' . $vm->name . '/' . $vm->uuid . ')');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        $computePool = VirtualMachinesService::getComputePool($vm);

        $command = 'xe vm-param-set name-label="' . $vm->uuid . '" uuid=' . $vm->hypervisor_uuid;

        //  If the iso27001 is not enabled, we can set the name-label to the VM name
        //  Otherwise, we need to set the name-label to the VM uuid
        if (!$computePool->is_iso27001_enabled)
            $command = 'xe vm-param-set name-label="' . $vm->name . '" uuid=' . $vm->hypervisor_uuid;

        $result = self::performCommand($command, $computeMember);

        $hypervisorParams = self::getVmParameters($vm);

        $vm->update([
            'hypervisor_data'   =>  $hypervisorParams
        ]);

        StateHelper::setState($vm, 'name', 'fixed');

        return true;
    }

    public static function syncVmDisks(VirtualMachines $vm): bool
    {
        $vbds = self::getVmDisks($vm);

        $computeMember = VirtualMachinesService::getComputeMember($vm);

        foreach ($vbds as $vbd) {
            //  Sometimes we get null values, we are skipping them (I dont know why)
            if($vbd == [])
                continue;

            if(array_key_exists('vdi-uuid', $vbd)) {
                $diskParams = VirtualDiskImageXenService::getDiskImageParametersByUuid($vbd['vdi-uuid'], $computeMember);
            }

            $vbdParams = VirtualDiskImageXenService::getDiskConnectionInformation($vbd['uuid'], $computeMember);

            //  We are taking CDROM if the vbd type is CDROM
            if($vbdParams['type'] === 'CD') {
                $dbVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('is_cdrom', true)
                    ->where('iaas_virtual_machine_id', $vm->id)
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
                'name'                      =>  $vbdParams['type'] !== 'CD' ? 'Disk of: ' . $vm->name : 'CDROM',
                'size'                      =>  $vbdParams['type'] !== 'CD' ? $diskParams['virtual-size'] : 0,
                'physical_utilisation'      =>  $vbdParams['type'] !== 'CD' ? $diskParams['physical-utilisation'] : 0,
                'iaas_storage_volume_id'    =>  $vbdParams['type'] !== 'CD' ? $diskVolume->iaas_storage_volume_id : null,
                'iaas_virtual_machine_id'   =>  $vm->id,
                'device_number'             =>  $vbdParams['userdevice'],
                'is_cdrom'                  =>  $vbdParams['type'] === 'CD',
                'hypervisor_uuid'       =>  $vbdParams['vdi-uuid'],
                'hypervisor_data'       =>  $diskParams ?? [],
                'iam_account_id'        =>  $vm->iam_account_id,
                'iam_user_id'           =>  $vm->iam_user_id,
                'is_draft'              =>  false,
                'vbd_hypervisor_uuid'   =>  $vbd['uuid'],
                'vbd_hypervisor_data'   =>  $vbdParams
            ];

            if($dbVdi)
                try {
                    $dbVdi->updateQuietly($data);
                } catch(\Exception $e) {
                    dump($dbVdi);
                    dump($data);
                    dd( $e->getMessage() );
                }
            else {
                //  We need to check if we already have a record with the iaas_virtual_machine_id and device_number
                //  If we have, we will update it, if not we will create a new one
                $checkVdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_virtual_machine_id', $vm->id)
                    ->where('device_number', $vbdParams['userdevice'])
                    ->first();

                //  This happens when the VDI is migrated to another storage. We need to update the hypervisor_uuid
                if($checkVdi) {
                    $checkVdi->updateQuietly($data);
                    $dbVdi = $checkVdi;
                }
                else
                    $dbVdi = VirtualDiskImages::create($data);
            }
        }

        return true;
    }

    public static function mountCD(VirtualMachines $vm, RepositoryImages $image, $isRawImage = true): bool
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@mountCD] I am mounting the' .
                ' CD (' . $image->name . '/' . $image->uuid . ') to the VM (' .
                $vm->name . '/' . $vm->uuid . ')');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        //  We need to scan the volume before we try to mount because sometimes the config ISO cannot be found
        $storageVolume = ComputeMemberStorageVolumes::withoutGlobalScopes()
            ->where('iaas_compute_member_id', $computeMember->id)
            ->where('name', 'NFS ISO library')
            ->first();

        if(!$storageVolume) {
            ComputeMemberXenService::updateStorageVolumes($computeMember);

            $storageVolume = ComputeMemberStorageVolumes::withoutGlobalScopes()
                ->where('iaas_compute_member_id', $computeMember->id)
                ->where('name', 'NFS ISO library')
                ->first();
        }

        $command = 'xe sr-scan uuid=' . $storageVolume->hypervisor_uuid;
        self::performCommand($command, $computeMember);

        $command = 'xe vm-cd-insert vm="' . $vm->hypervisor_data['name-label'] . '" cd-name=' . $image->filename;

        if (config('leo.debug.iaas.compute_members'))
            Log::debug('[VirtualMachinesXenService@mountCD] Mount command: ' . $command);

        $result = self::performCommand($command, $computeMember);

        if($result['output'] == '') {
            //  This means that we dont have CD mounted on the server. We will mount a cdrom with device number default 3
            //  If we make it 255, older operating systems does not see the CDROM
            $command = 'xe vm-cd-add vm="' . $vm->hypervisor_data['name-label'] . '" cd-name=' . $image->filename . ' device=3';

            if (config('leo.debug.iaas.compute_members'))
                Log::debug('[VirtualMachinesXenService@mountCD] Mount command: ' . $command);

            $result = self::performCommand($command, $computeMember);

            self::syncVmDisks($vm);
        }

        if($isRawImage) {
            $command = 'xe vm-param-set uuid=' . $vm->hypervisor_data['uuid'] . ' other-config:cdrom-config-raw=true';
            $result = self::performCommand($command, $computeMember);
        }

        if (config('leo.debug.iaas.compute_members'))
            Log::debug('[VirtualMachinesXenService@mountCD] Mount command result: ' .
                json_encode($command));

        $checkCommand = 'xe vm-cd-list vm="' . $vm->hypervisor_data['name-label'] . '"';
        $command = self::performCommand($checkCommand, $computeMember);
        $result = self::parseListResult($command['output']);

        if (config('leo.debug.iaas.compute_members'))
            Log::debug('[VirtualMachinesXenService@mountCD] Check command result: ' .
                json_encode($result));

        $cdrom = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('is_cdrom', 'true')
            ->where('iaas_virtual_machine_id', $vm->id)
            ->first();

        if (count($result) > 1) {
            if (array_key_exists('CD 0 VDI', $result[1])) {
                $cdrom->update([
                    'hypervisor_uuid' => $result[1]['uuid'],
                    'name' => 'CD: ' . $result[1]['name-label'],
                    'size' => $result[1]['virtual-size']
                ]);

                return true;
            }
        }

        if($cdrom) {
            $cdrom->update([
                'hypervisor_uuid' => null,
                'name' => 'CDROM',
                'size' => 0
            ]);
        }

        return false;
    }

    public static function unmountCD(VirtualMachines $vm)
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@unmountCD] I am unmounting the' .
                ' CD from the VM (' . $vm->name . '/' . $vm->uuid . ')');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        $command = 'xe vm-cd-eject vm="' . $vm->hypervisor_data['name-label'] . '"';
        $command = self::performCommand($command, $computeMember);

        $checkCommand = 'xe vm-cd-list vm="' . $vm->hypervisor_data['name-label'] . '"';
        $command = self::performCommand($checkCommand, $computeMember);
        $result = self::parseListResult($command['output']);

        $cdrom = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('is_cdrom', 'true')
            ->where('iaas_virtual_machine_id', $vm->id)
            ->first();

        if (count($result) > 1) {
            if (array_key_exists('CD 0 VDI', $result[1]))
                return false;
        }

        $cdrom->update([
            'hypervisor_uuid' => null,
            'name' => 'CDROM',
            'size' => 0
        ]);

        return true;
    }

    public static function updateConfigurationIso(VirtualMachines $vm): bool
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@updateConfigurationIso] I am updating the' .
                ' configuration ISO of the VM (' . $vm->name . '/' . $vm->uuid . ')');

        //  Here if we have a central repository server we should move it to that repository
        $centralRepo = RepositoriesService::getIsoRepoForVirtualMachine($vm);

        if ($centralRepo) {
            //  Creating the configuration folder
            $command = 'mkdir config-iso/' . $vm->uuid . ' -p';
            $result = self::performCommand($command, $centralRepo);

            $uploadConfig = function($filename, $content, $vm, $centralRepo) {
                //  Pushing the user-data file
                $command = 'echo "' . $content . '" > config-iso/' . $vm->uuid . '/' . $filename . '.base64';
                $result = self::performCommand($command, $centralRepo);

                //  Decoding the user-data file
                $command = 'base64 -d config-iso/' . $vm->uuid . '/' . $filename . '.base64 > config-iso/' . $vm->uuid . '/' . $filename . '';
                $result = self::performCommand($command, $centralRepo);
            };

            $uploadConfig(
                filename: 'pc-meta-data.json',
                content: base64_encode(json_encode(VirtualMachinesService::getMetadata($vm))),
                vm: $vm,
                centralRepo: $centralRepo
            );

            $uploadConfig(
                filename: 'user-data',
                content: base64_encode(VirtualMachinesService::getCloudInitConfiguration($vm)),
                vm: $vm,
                centralRepo:  $centralRepo
            );

            $uploadConfig(
                filename: 'meta-data',
                content: base64_encode('instance-id: ' . $vm->uuid . "\n" . 'local-hostname: ' . $vm->hostname . "\n"),
                vm: $vm,
                centralRepo: $centralRepo
            );

            $configurationPack = [
                'apply-configuration.yml',
                'apply-locale.yml',
                'change-hostname.yml',
                'change-password.yml',
                'disk-resize-debian12.yml',
                'disk-resize-ubuntu22.yml',
                'disk-resize-ubuntu24.yml',
                'run-post-boot-script.yml'
            ];

            foreach ($configurationPack as $pack) {
                $uploadConfig(
                    filename: $pack,
                    content: base64_encode(file_get_contents(base_path('vendor/nextdeveloper/iaas/scripts/vm-service/' . $pack))),
                    vm: $vm,
                    centralRepo: $centralRepo
                );
            }

            logger()->info('[VirtualMachineXenService@updateConfigurationIso] Is updating the post_book_script?]');

            logger()->info('[VirtualMachineXenService@updateConfigurationIso] Post boot script is: ' . $vm->post_boot_script);

            //  Here we are creating the post boot script if the VM has it
            if($vm->post_boot_script) {
                $script = $vm->post_boot_script;

                $uploadConfig(
                    filename: 'post-boot-script.sh',
                    content: base64_encode($script),
                    vm: $vm,
                    centralRepo: $centralRepo
                );
            } else {
                logger()->info('[VirtualMachineXenService@updateConfigurationIso] We are not updating the post_book_script.]');
            }

            //  Creating the iso file
            $command = 'genisoimage -output ' .
                'config-iso/' . $vm->uuid . '/config.iso ' .
                '-volid cidata -joliet -rock ' .
                'config-iso/' . $vm->uuid . '/user-data ' .
                'config-iso/' . $vm->uuid . '/meta-data ' .
                'config-iso/' . $vm->uuid . '/pc-meta-data.json ';

            if($vm->post_boot_script) {
                $command .= 'config-iso/' . $vm->uuid . '/post-boot-script.sh';
            }

            foreach ($configurationPack as $pack) {
                $command .= ' config-iso/' . $vm->uuid . '/' . $pack;
            }

            $result = self::performCommand($command, $centralRepo);

            //  removing .base64 files
            $command = 'rm -f config-iso/' . $vm->uuid . '/*.base64';
            $result = self::performCommand($command, $centralRepo);

            //  Moving the iso to the central repository
            $command = 'mv config-iso/' . $vm->uuid . '/config.iso ' . $centralRepo->iso_path . '/config-' . $vm->uuid . '.iso';
            $result = self::performCommand($command, $centralRepo);

            //  Removing the config-iso folder
            $command = 'rm -f config-iso/' . $vm->uuid . '/config.iso';
            $result = self::performCommand($command, $centralRepo);

            $configImage = RepositoryImagesService::getCloudInitImage($vm);

            if(!$configImage) {
                RepositoryImagesService::syncRepoImageByFilename(
                    filename: 'config-' . $vm->uuid . '.iso',
                    repo: $centralRepo,
                    type: 'iso',
                    isActive: true
                );
            }

            return true;
        }

        return false;
    }

    public static function exportToRepositoryInBackground(
        VirtualMachines $vm,
        Repositories $repositories,
        $exportName,
        VirtualMachineBackups $vmBackup
    ): bool
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@export] I am exporting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') to default repo under name ' .
                $exportName . '.backup from compute member' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        /**
         * # Run the entire command in the background, ensuring it continues even after logout
         * nohup bash -c '
         *
         * # 1️⃣ Export a virtual machine from XenServer by UUID
         * xe vm-export \
         * uuid=ac27e957-fec6-43e8-d083-c7cdac6f0094 \
         * filename=/mnt/plusclouds-repo/2e4bdaca-cdc4-4665-9ace-564b1f0c265b/69abaf66-c4f0-4d87-bb12-934ac117fbb4.1761867357.pvm \
         *
         * # 2️⃣ If export succeeds (&&), send a POST request to notify completion
         * && curl -X POST http://10.1.32.5:8011/public/iaas/finalize-backup/4171e717-7f97-4262-9707-d4f916b39b71
         * ' \
         *
         * # Redirect all standard output (stdout) and error (stderr) to /dev/null (ignore all logs)
         * > /dev/null 2>&1 \
         *
         * # Run the whole process in the background (asynchronously)
         * &
         */

        //  This is the background version of the command with &
        $command = 'nohup bash -c \'xe vm-export uuid=' . $vm->hypervisor_uuid . ' ' .
            'filename=/mnt/plusclouds-repo/' . $repositories->uuid . '/' . $exportName . ' &&' .
            ' curl -X POST ' . config('leo.internal_endpoint') . '/public/iaas/finalize-backup/' . $vmBackup->uuid . '\'' .
            ' > /dev/null 2>&1 &';

        Log::info(__METHOD__ . ' Exporting with command: ' . $command);

        $result = self::performCommand($command, $computeMember);

        return true;
    }

    public static function exportToRepository(VirtualMachines $vm, Repositories $repositories, $exportName): bool
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        //  Here we need to make sure that there are no export tasks running on the compute member.
        //  If there are, we need to wait until they are finished.
        $isTaskRunning = self::isBackupRunning($computeMember, $vm->name);

        if($isTaskRunning) {
            while ($isTaskRunning) {
                if (config('leo.debug.iaas.compute_members'))
                    Log::info('[VirtualMachinesXenService@export] There is already a' .
                        ' backup task running for VM: ' . $vm->name . '/' . $vm->uuid);

                sleep(10);
                $isTaskRunning = self::isBackupRunning($computeMember, $vm->name);
            }

            return true;
        }

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@export] I am exporting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') to default repo under name ' .
                $exportName . '.backup from compute member' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-export uuid=' . $vm->hypervisor_uuid . ' ' .
            'filename=/mnt/plusclouds-repo/' . $repositories->uuid . '/' . $exportName;

        Log::info(__METHOD__ . ' Exporting with command: ' . $command);

        $result = self::performCommand($command, $computeMember);

        return true;
    }

    public static function exportToDefaultBackupRepository(VirtualMachines $vm): array
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $backupName = $vm->uuid . '.' . (new Carbon($vm->created_at))->timestamp . '.backup';

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@export] I am exporting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') to default repo under name ' .
                $backupName . '.backup from compute member' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-export uuid=' . $vm->hypervisor_uuid . ' ' .
            'filename=/mnt/plusclouds-backup-repo/' . $backupName;

        $result = self::performCommand($command, $computeMember);

        $result['filename'] = $backupName;
        $result['path'] = 'default-backup-repo://' . $backupName;

        return $result;
    }

    public static function export(VirtualMachines $vm, Repositories $repo): string
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@export] I am exporting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $newUuid = uuid_create(UUID_TYPE_DEFAULT);

        $command = 'xe vm-export uuid=' . $vm->hypervisor_uuid . ' ' .
            'filename=/mnt/plusclouds-repo/' . $repo->uuid . '/' . $newUuid . '.pvm';
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return $newUuid;
    }

    public static function getVmParameters(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmParameters] I am taking the' .
                ' parameters of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-param-list uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function checkIfVmIsThere(VirtualMachines $vm): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        //  This means that we dont have the compute member. So we return false.
        if (!$computeMember)
            return false;

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmParameters] I am taking the' .
                ' parameters of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-param-list uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        if (Str::contains($result['error'], 'The uuid you supplied was invalid'))
            return false;

        return true;
    }

    public static function getVmParametersByUuid(ComputeMembers $computeMember, $vmUuid): array
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmParametersByUuid] I am taking the' .
                ' parameters of the VM (' . $vmUuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-param-list uuid=' . $vmUuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function getVmDisks(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmDisks] I am taking the' .
                ' disks of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vbd-list vm-uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $list = self::parseListResult($result['output']);

        return $list;
    }

    public static function getVifs(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmDisks] I am taking the' .
                ' vifs of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vif-list vm-uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $list = self::parseListResult($result['output']);

        return $list;
    }

    public static function getVifParams(VirtualMachines $vm, $uuid)
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getVmDisks] I am taking the' .
                ' vif params of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vif-param-list uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);
        $list = self::parseListResult($result['output']);

        return $list;
    }

    public static function createVif(VirtualMachines $vm, $networkUuid, $device)
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@createVif] I am creating the' .
                ' vif from network (' . $networkUuid . ') for the VM (' . $vm->name . '/' . $vm->uuid . ')');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        $command = 'xe vif-create vm-uuid=' . $vm->hypervisor_uuid . ' device=' . $device . ' ' .
            'network-uuid=' . $networkUuid;

        $result = self::performCommand($command, $computeMember);

        Log::info('[' . __METHOD__ . '] The result of creating the VIF is: ' . print_r($result, true));

        return $result['output'];
    }

    public static function destroyVif(VirtualMachines $vm, $uuid)
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@destroyVif] I am destroying the' .
                ' vif (' . $uuid . ') of the VM (' . $vm->name . '/' . $vm->uuid . ')');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        $command = 'xe vif-destroy uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return true;
    }

    /**
     * Setting the CPU for this Virtual Machine
     *
     * @param VirtualMachines $vm
     * @param int $coreCount Core count for this VM like 16 cores.
     * @param int $corePerSocket If not given we will distribute cores evenly
     * @return VirtualMachines
     */
    public static function setCPUCore(VirtualMachines $vm, $coreCount, $corePerSocket = null): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@setCPUCore] I am updating the' .
                ' CPU of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        //  Setting vCPU max
        $command = 'xe vm-param-set VCPUs-max=' . $coreCount . ' uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        //  Setting vCPU on boot
        $command = 'xe vm-param-set VCPUs-at-startup=' . $coreCount . ' uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        if ($corePerSocket) {
            $corePerSocket = (int)$corePerSocket;
            //  Right now we are assuming that there is 2 CPUs only. We can change this later
            //  @todo: change this later to dynamic
            //  $corePerSocket = $coreCount / 2;

            $command = 'xe vm-param-set platform:cores-per-socket=' . $coreCount . ' uuid=' . $vm->hypervisor_uuid;
            $result = self::performCommand($command, $computeMember);
            $result = $result['output'];
        }

        return true;
    }

    /**
     * Sets the ram
     *
     * @param VirtualMachines $vm
     * @param int $ram MB of ram requestes
     * @return VirtualMachines
     */
    public static function setRam(VirtualMachines $vm, $ram): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@setRam] I am updating the' .
                ' CPU of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        //  Converting GB to Bytes
        $ramBytes = $ram * 1024 * 1024;

        //  Setting RAM
        $command = 'xe vm-memory-limits-set static-min=' . $ramBytes;
        $command .= ' dynamic-min=' . $ramBytes;
        $command .= ' dynamic-max=' . $ramBytes;
        $command .= ' static-max=' . $ramBytes;
        $command .= ' uuid=' . $vm->hypervisor_uuid;

        if (config('leo.debug.iaas.compute_members'))
            logger()->info('[VirtualMachineService@setRam] Executing command: ' . $command);

        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public static function getConsoleParameters(VirtualMachines $vm): array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@getConsoleParameters] I am taking the' .
                ' console parameters of the VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe console-list vm-uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseListResult($result['output']);
    }

    public static function syncVirtualNetworkCards(VirtualMachines $vm): bool
    {
        $vifs = VirtualMachinesXenService::getVifs($vm);

        foreach ($vifs as $vif) {
            if ($vif == [])
                continue;

            $vifParams = VirtualMachinesXenService::getVifParams($vm, $vif['uuid']);

            if (array_key_exists(0, $vifParams))
                $vifParams = $vifParams[0];

            $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $vif['uuid'])
                ->first();

            $connectedInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('network_uuid', $vifParams['network-uuid'])
                ->first();

            if (!$connectedInterface) {
                $computeMember = VirtualMachinesService::getComputeMember($vm);
                //  Here we will add another trigger to scan all compute member network interfaces
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[ScanVirtualMachines] Cannot find the connected ' .
                    'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                    'should be scanned and synced immediately.');

                continue;
            }

            $computePool = VirtualMachinesService::getComputePool($vm);

            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $connectedInterface->vlan)
                ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
                ->first();

            if (!$network) {
                //  Here we need to create another scan and create the related network
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[ScanVirtualMachines] Cannot find the connected ' .
                    'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                    'should be scanned and synced immediately.');

                continue;
            }

            $data = [
                'name' => 'eth' . $vifParams['device'],
                'device_number' => $vifParams['device'],
                'mac_addr' => $vifParams['MAC'],
                'bandwidth_limit' => '-1', //$vifParams['qos_algorithm_params']['kbps'],
                'iaas_network_id' => $network->id,
                'hypervisor_uuid' => $vif['uuid'],
                'hypervisor_data' => $vif,
                'iam_account_id' => $vm->iam_account_id,
                'iam_user_id' => $vm->iam_user_id,
                'is_draft' => false,
                'iaas_virtual_machine_id' => $vm->id
            ];

            if ($dbVif)
                $dbVif->update($data);
            else
                VirtualNetworkCardsService::create($data);
        }

        return true;
    }

    public static function performCommand($command, Repositories|ComputeMembers $computeMember): ?array
    {
        try {
            if ($computeMember->is_management_agent_available == true) {
                return $computeMember->performAgentCommand($command);
            } else {
                $log = [
                    'command'   =>  $command,
                    'member'    =>  $computeMember->name
                ];

                $result = $computeMember->performSSHCommand($command);

                $log['output'] = $result['output'];
                $log['error'] = $result['error'];

                Log::debug(print_r($log, true));

                return $result;
            }
        } catch (CannotConnectWithSshException $exception) {
            Log::error(__METHOD__ . 'There is an error in performing the command: ' . $command .
                ' on the compute member: ' . $computeMember->name . '/' . $computeMember->uuid .
                '. The error is: ' . $exception->getMessage());

            Log::debug(__METHOD__ . ' Running the health check for the compute member: ' .
                $computeMember->name . '/' . $computeMember->uuid);

            throw $exception;
        } catch (\Exception $exception) {
            Log::error(__METHOD__ . 'There is an error in performing the command: ' . $command . '' .
                ' on the compute member: ' . $computeMember->name . '/' . $computeMember->uuid .
                '. The error is: ' . $exception->getMessage());

            throw $exception;
        }
    }

    public static function isBackupRunning($computeMember, $vmName)  : ?float
    {
        $runningTasks = ComputeMemberXenService::getRunningTasks($computeMember);
        $isBackupRunning = false;

        Log::debug('[isBackupRunning] response: ' . print_r($runningTasks, true));

        foreach ($runningTasks as $task) {
            Log::debug('[isBackupRunning] looking for: ' . 'Export of VM: ' . $vmName);
            $task['name-label'] = trim($task['name-label']);
            if($task['name-label'] == 'Export of VM: ' . $vmName) {
                return floatval($task['progress']);
            }
        }

        return null;
    }
}
