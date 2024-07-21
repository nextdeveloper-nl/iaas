<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;

class VirtualDiskImageXenService extends AbstractXenService
{
    public static function destroyCdrom($vmName, $computeMember) {
        logger()->info('[VirtualDiskImageService@destroyCdrom] Destroying the cdrom with ' .
            'vmName: ' . $vmName);

        $command = 'xe vm-cd-eject vm=' . $vmName;
        $result = self::performCommand($command, $computeMember);

        $command = 'xe vm-cd-remove vm=' . $vmName . ' cd-name=\<EMPTY\>';
        $result = self::performCommand($command, $computeMember);

        logger()->info('[VirtualDiskImageService@destroyCdrom] Returned result as: ' . $result[0]['output']);

        return self::parseResult($result[0]['output']);
    }

    public static function ejectCdrom($uuid, $computeMember)
    {
        logger()->info('[VirtualDiskImageService@ejectCdrom] Ejecting the cdrom with ' .
            'hypervisor_uuid: ' . $uuid);

        $command = 'xe vm-cd-eject uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function destroyDisk($uuid, $computeMember)
    {
        logger()->info('[VirtualDiskImageService@destroyDisk] Destroying the disk with ' .
            'hypervisor_uuid: ' . $uuid);

        $command = 'xe vdi-destroy uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function resize($uuid, $computeMember, $size) : array
    {
        logger()->info('[VirtualDiskImageService@resize] Resizing the disk with ' .
            'hypervisor_uuid: ' . $uuid);

        $command = 'xe vdi-resize uuid='. $uuid . ' disk-size=' . $size;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function getDiskImageParametersByUuid($uuid, $computeMember) : array
    {
        $command = 'xe vdi-param-list uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function getDiskConnectionInformation($uuid, $computeMember) : array
    {
        $command = 'xe vbd-param-list uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result[0]['output']);
    }

    public static function performCommand($command, ComputeMembers $computeMember) : ?array
    {
        if($computeMember->is_management_agent_available == true) {
            return $computeMember->performAgentCommand($command);
        } else {
            return $computeMember->performSSHCommand($command);
        }
    }
}
