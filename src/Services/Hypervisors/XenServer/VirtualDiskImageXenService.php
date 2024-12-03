<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class VirtualDiskImageXenService extends AbstractXenService
{
    public static function destroyCdrom($vmName, $computeMember) {
        logger()->info('[VirtualDiskImageService@destroyCdrom] Destroying the cdrom with ' .
            'vmName: ' . $vmName);

        $command = 'xe vm-cd-eject vm=' . $vmName;
        $result = self::performCommand($command, $computeMember);

        $command = 'xe vm-cd-remove vm=' . $vmName . ' cd-name=\<EMPTY\>';
        $result = self::performCommand($command, $computeMember);

        logger()->info('[VirtualDiskImageService@destroyCdrom] Returned result as: ' . $result['output']);

        return self::parseResult($result['output']);
    }

    public static function ejectCdrom($uuid, $computeMember)
    {
        logger()->info('[VirtualDiskImageService@ejectCdrom] Ejecting the cdrom with ' .
            'hypervisor_uuid: ' . $uuid);

        $command = 'xe vm-cd-eject uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function destroyDisk($uuid, $computeMember)
    {
        logger()->info('[VirtualDiskImageService@destroyDisk] Destroying the disk with ' .
            'hypervisor_uuid: ' . $uuid);

        $command = 'xe vdi-destroy uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function resize($uuid, $computeMember, $size) : array
    {
        logger()->info('[VirtualDiskImageService@resize] Resizing the disk with ' .
            'hypervisor_uuid: ' . $uuid);

        $command = 'xe vdi-resize uuid='. $uuid . ' disk-size=' . $size;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function getDiskImageParametersByUuid($uuid, $computeMember) : array
    {
        $command = 'xe vdi-param-list uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function getDiskConnectionInformation($uuid, $computeMember) : array
    {
        $command = 'xe vbd-param-list uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    public static function performCommand($command, ComputeMembers $computeMember) : ?array
    {
        if($computeMember->is_management_agent_available == true) {
            return $computeMember->performAgentCommand($command);
        } else {
            return $computeMember->performSSHCommand($command);
        }
    }

    public static function create(VirtualDiskImages $vdi) : VirtualDiskImages
    {
        if(!$vdi->is_draft) {
            Log::warning(__METHOD__ . ' The disk which is asked to create is not a draft disk.');
            return $vdi;
        }

        if($vdi->hypervisor_uuid) {
            Log::warning(__METHOD__ . ' The disk which is asked to create has already a hypervisor_uuid. ' .
                'Maybe we need to sync this disk or trigger sync for the VM!');

            return $vdi;
        }

        $computeMember = VirtualDiskImagesService::getComputeMember($vdi);

        $cmVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->where('iaas_storage_pool_id', $vdi->iaas_storage_pool_id)
            ->first();

        $command = 'xe vdi-create name-label=' . $vdi->uuid;
        $command .= ' sr-uuid=' . $cmVolume->hypervisor_uuid;
        $command .= ' type=user';
        $command .= ' virtual-size=' . $vdi->size;

        Log::info('[VirtualDiskImageService@create] Creating the VDI with this command: ' . $command . ' on this '
            . 'compute member uuid: ' . $computeMember->uuid . ' and name: ' . $computeMember->name);

        $result = $computeMember->performSSHCommand($command);

        if(!$result['error']) {
            $vdiParams = self::getDiskImageParametersByUuid($result['output'], $computeMember);

            $vdi->updateQuietly([
                'hypervisor_uuid'   =>  $result['output'],
                'hypervisor_data'   =>  $vdiParams
            ]);
        } else {
            Log::error(__METHOD__ . ' | VDI Create error: ' . $result['error']);
        }

        return $vdi;
    }

    public static function attach($vdi) : VirtualDiskImages
    {
        $vdi->refresh();
        $vm = VirtualDiskImagesService::getVirtualMachine($vdi);
        $computeMember = VirtualDiskImagesService::getComputeMember($vdi);

        Log::info('[VirtualDiskImageService@attach] We are trying to attach the disk uuid: ' . $vdi->uuid
            . ' to the virtual machine uuid: ' . $vm->uuid);

        if(!$vdi->vbd_hypervisor_uuid) {
            $command = 'xe vbd-create vm-uuid=' . $vm->hypervisor_uuid . ' ';
            $command .= 'vdi-uuid=' . $vdi->hypervisor_uuid . ' ';
            $command .= 'device=' . $vdi->device_number;

            $result = $computeMember->performSSHCommand($command);

            if($result['error']) {
                Log::error(__METHOD__ . ' | We have an error with create virtual block device: '
                    . $result['error']);

                return $vdi;
            }

            $vdi->updateQuietly([
                'vbd_hypervisor_uuid'   =>  $result['output']
            ]);
        }

        if($vm->status == 'running') {
            $command = 'xe vbd-plug uuid=' . $vdi->vbd_hypervisor_uuid;

            $result = $computeMember->performSSHCommand($command);

            if($result['error']) {
                Log::error(__METHOD__ . ' | There is an error when creating the VBD for VDI: ' . $result['error']);

                //  Not returning VDI here because even if we have an error, there is a chance that VDI can be mounted
                //  automatically by hypervisor. Therefor we continue the process.
                //return $vdi;
            }
        }

        $vdi->refresh();

        $vdi->updateQuietly([
            'vbd_hypervisor_data'   =>  self::getDiskConnectionInformation(
                $vdi->vbd_hypervisor_uuid,
                $computeMember
            )
        ]);

        return $vdi;
    }
}
