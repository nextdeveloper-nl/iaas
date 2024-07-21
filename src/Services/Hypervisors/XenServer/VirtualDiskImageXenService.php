<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\StorageMembersService;
use NextDeveloper\IAAS\Services\StoragePoolsService;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use PlusClouds\IAAS\Services\XenServer\XenServerService;

class VirtualDiskImageXenService extends AbstractXenService
{
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
