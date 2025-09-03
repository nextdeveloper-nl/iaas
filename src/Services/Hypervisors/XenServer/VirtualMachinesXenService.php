<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\RepositoriesService;
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

        $command = 'xe vm-snapshot vm=' . $vm->hypervisor_data['name-label'] . ' new-name-label=' . $name;
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

        $command = 'xe vm-clone vm=' . $vm->uuid . ' new-name-label=cloned-' . $vm->uuid;
        $result = self::performCommand($command, $computeMember);

        return $result;
    }

    public static function fixName(VirtualMachines $vm): bool
    {
        if (StateHelper::getState($vm, 'name') == 'fixed')
            return true;

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

        StateHelper::setState($vm, 'name', 'fixed');

        return true;
    }

    public static function mountCD(VirtualMachines $vm, RepositoryImages $image): bool
    {
        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@mountCD] I am mounting the' .
                ' CD (' . $image->name . '/' . $image->uuid . ') to the VM (' .
                $vm->name . '/' . $vm->uuid . ')');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        $command = 'xe vm-cd-insert vm="' . $vm->hypervisor_data['name-label'] . '" cd-name=' . $image->filename;

        if (config('leo.debug.iaas.compute_members'))
            Log::debug('[VirtualMachinesXenService@mountCD] Mount command: ' . $command);

        $command = self::performCommand($command, $computeMember);

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

        $cdrom->update([
            'hypervisor_uuid' => null,
            'name' => 'CDROM',
            'size' => 0
        ]);

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

        $computeMember = VirtualMachinesService::getComputeMember($vm);

        //  Here if we have a central repository server we should move it to that repository
        $centralRepo = RepositoriesService::getIsoRepoForVirtualMachine($vm);

        if ($centralRepo) {
            //  Here we will write the user-data config to the xenserver before we create the iso
            $userData = VirtualMachinesService::getCloudInitConfiguration($vm);
            $base64UserData = base64_encode($userData);

            //  Creating the configuration folder
            $command = 'mkdir config-iso/' . $vm->uuid . ' -p';
            $result = self::performCommand($command, $centralRepo);

            //  Pushing the user-data file
            $command = 'echo "' . $base64UserData . '" > config-iso/' . $vm->uuid . '/user-data.base64';
            $result = self::performCommand($command, $centralRepo);

            //  Decoding the user-data file
            $command = 'base64 -d config-iso/' . $vm->uuid . '/user-data.base64 > config-iso/' . $vm->uuid . '/user-data';
            $result = self::performCommand($command, $centralRepo);

            //  Pushing the meta-data content to the file
            $metaDataBase64 = base64_encode('instance-id: ' . $vm->uuid . "\n" . 'local-hostname: ' . $vm->hostname . "\n");
            $command = 'echo "' . $metaDataBase64 . '" > config-iso/' . $vm->uuid . '/meta-data.base64';
            $result = self::performCommand($command, $centralRepo);

            //  Decoding the meta-data file
            $command = 'base64 -d config-iso/' . $vm->uuid . '/meta-data.base64 > config-iso/' . $vm->uuid . '/meta-data';
            $result = self::performCommand($command, $centralRepo);

            //  Creating the iso file
            $command = 'genisoimage -output config-iso/' . $vm->uuid . '/config.iso -volid CIDATA -joliet -rock config-iso/' . $vm->uuid . '/user-data config-iso/' . $vm->uuid . '/meta-data';
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

            return true;
        }

        return false;
    }

    public static function exportToRepository(VirtualMachines $vm, Repositories $repositories): array
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $exportName = $vm->uuid . '.' . (new Carbon($vm->created_at))->timestamp . '.pvm';

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@export] I am exporting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') to default repo under name ' .
                $exportName . '.backup from compute member' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-export uuid=' . $vm->hypervisor_uuid . ' ' .
            'filename=/mnt/plusclouds-repo/' . $repositories->uuid . '/' . $exportName;

        Log::info(__METHOD__ . ' Exporting with command: ' . $command);

        $result = self::performCommand($command, $computeMember);

        $result['filename'] = $exportName;
        $result['path'] = $repositories->local_ip_addr . ':' . $repositories->vm_path . '/' . $exportName;

        return $result;
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
                if (config('leo.debug.iaas.compute_members'))
                    logger()->debug('[' . __METHOD__ . '] Performing command via SSH: ' . $command);

                $result = $computeMember->performSSHCommand($command);

                if (config('leo.debug.iaas.compute_members'))
                    logger()->debug('[' . __METHOD__ . '] Result: ' . print_r($result, true));
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
}
