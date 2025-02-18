<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use NextDeveloper\IAAS\Exceptions\SynchronizationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotImportException;
use NextDeveloper\IAAS\Exceptions\NetworkNotInPoolException;
use NextDeveloper\IAAS\Helpers\NetworkCalculationHelper;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\ComputeMemberStorageVolumesService;
use NextDeveloper\IAAS\Services\NetworksService;
use NextDeveloper\IAAS\Services\StorageMembersService;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use phpseclib3\File\ASN1\Maps\FieldID;

class ComputeMemberXenService extends AbstractXenService
{
    public static function updateMemberInformation(ComputeMembers $computeMember) : ComputeMembers
    {
        Log::info('[ComputeMemberService@sync] Checking if we can connect to: ' . $computeMember->name);

        try {
            $command = 'hostname';
            $hostname = self::performCommand($command, $computeMember);
            $hostname = $hostname['output'];
        } catch (\Exception $e) {
            Log::error('[ComputeMemberService@sync] Cannot connect to the host: ' . $computeMember->name);
            StateHelper::setState(
                $computeMember,
                'netconf_connection_problem',
                'has_errors',
                StateHelper::STATE_ERROR,
                'Cannot connect to the host: ' . $computeMember->name
            );
            return $computeMember;
        }

        //  This command will give us the uptime of the host as timestamp
        $command = 'stat -c %Z /proc/ ';
        $uptime = self::performCommand($command, $computeMember);
        $uptime = $uptime['output'];

        $command = 'xe host-list';
        $hostlist = self::performCommand($command, $computeMember);
        $hostListArray = self::parseListResult($hostlist['output']);

        $hypervisor = null;

        foreach ($hostListArray as $host) {
            $command = 'xe host-param-list uuid=' . $host['uuid'];
            $hostInfo = self::performCommand($command, $computeMember);
            $hostInfo = self::parseResult($hostInfo['output']);

            //  We are checking this because this host can be a part of a pool and we need to get the correct host
            if($hostInfo['hostname'] == $hostname) {
                $hypervisor = $hostInfo;
                break;
            }
        }

        Log::info('[ComputeMemberService@sync] We got the correct host: ' . $hypervisor['name-label']);
        Log::info('[ComputeMemberService@sync] Going to update compute member information');

        $cpuInformation = self::parseDeviceConfigParameters($hypervisor['cpu_info']);
        $softwareInformation = self::parseDeviceConfigParameters($hypervisor['software-version']);

        $computeMember->update([
            'name'  => $hypervisor['name-label'],
            'hostname'  =>  $hypervisor['hostname'],
            'hypervisor_uuid'   =>  $hypervisor['uuid'],
            'hypervisor_data'   =>  $hypervisor,
            'uptime'            =>  $uptime,
            'total_ram'         =>  ceil($hypervisor['memory-total']  / 1024 / 1024 / 1024),
            'used_ram'          =>  ceil(($hypervisor['memory-total'] - $hypervisor['memory-free']) / 1024 / 1024 / 1024),
            'total_cpu'         =>  $cpuInformation['cpu_count'],
            'total_socket'      =>  $cpuInformation['socket_count'],
            'hypervisor_model'  =>  'XenServer ' . trim($softwareInformation['product_version_text_short']),
            'cpu_info'          =>  $cpuInformation,
            'overbooking_ratio' => $computeMember->overbooking_ratio == 0 ? 15 : $computeMember->overbooking_ratio,
        ]);

        return $computeMember->fresh();
    }

    public static function updateConnectionInformation(ComputeMembers $computeMembers) : ComputeMembers
    {
        $mgmtInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('is_management', true)
            ->where('iaas_compute_member_id', $computeMembers->id)
            ->first();

        if(!$mgmtInterface)
            dd($computeMembers);

        $data = $mgmtInterface->hypervisor_data;

        $computeMemberIp = $computeMembers->ip_addr;

        if(Str::contains($computeMemberIp, '/'))
            $computeMemberIp = explode('/', $computeMembers->ip_addr)[0];

        $computeMembers->update([
            'ip_addr'   =>  $computeMemberIp . '/' . NetworkCalculationHelper::mask2cidr($data['netmask']),
            'local_ip_addr' =>  $data['IP'] . '/' . NetworkCalculationHelper::mask2cidr($data['netmask']),
            'is_behind_firewall'    =>  $data['IP'] != $computeMemberIp ? true : false,
        ]);

        return $computeMembers->fresh();
    }

    public static function updateInterfaceInformation(ComputeMembers $computeMember) : ComputeMembers
    {
        logger()->info('[ComputeMemberService@updateInterfaceInformation] We are starting to sync
physical interfaces and vlans of compute member');

        $command = 'xe pif-list';
        $result = self::performCommand($command, $computeMember);
        $interfaces = self::parseListResult($result['output']);

        foreach ($interfaces as $interface) {
            $command = 'xe pif-param-list uuid=' . $interface['uuid'];
            $result = self::performCommand($command, $computeMember);
            $interfaceDetail = self::parseResult($result['output']);

            $data = [
                'device'    =>  $interfaceDetail['device'],
                'mac_addr'  =>  $interfaceDetail['MAC'],
                'vlan'       =>  $interfaceDetail['VLAN'] ?? 0,
                'mtu'           =>  $interfaceDetail['MTU'],
                'is_management' =>  $interfaceDetail['management'] == 'true',
                'is_connected'  =>  $interfaceDetail['currently-attached'] == 'true',
                'is_default'    =>  $interfaceDetail['management'] == 'true',
                'is_bridge'     =>  false,
                'speed'         =>  $interfaceDetail['speed'],
                'hypervisor_data'   =>  $interfaceDetail,
                'hypervisor_uuid'   =>  $interfaceDetail['uuid'],
                'network_uuid'      =>  $interfaceDetail['network-uuid'],
                'network_name'      =>  $interfaceDetail['network-name-label'],
                'iaas_compute_member_id'    =>  $computeMember->id,
                'iam_account_id'    =>  $computeMember->iam_account_id,
                'iam_user_id'       =>  $computeMember->iam_user_id
            ];

            Log::info('[ComputeMemberService@updateInterfaceInformation] Syncing interface: '
                . $interfaceDetail['device'] . ' for compute member: ' . $computeMember->name
                . ' with details: ' . print_r($data, true));

            Log::info('[ComputeMemberService@updateInterfaceInformation] ' . $interfaceDetail['IP'] . '/' . NetworkCalculationHelper::mask2cidr($interfaceDetail['netmask']));

            if($data['is_management']) {
                $netCidr = NetworkCalculationHelper::mask2cidr($interfaceDetail['netmask']);
                $localIp= $interfaceDetail['IP'];

                if(intval($netCidr) != $netCidr) {
                    Log::error(__METHOD__ . ' | There is a serious network error. The netmask value most probably wrong.');
                    StateHelper::setState(
                        $computeMember,
                        'netconf_netmask_problem',
                        'has_errors',
                        StateHelper::STATE_ERROR,
                        'Most probably network configuration is wrong. Please make sure that the ' .
                        'network config is correct! Because the netmask operation is not an integer. ' .
                        'Therefore I assume that netmask value is wrong. I am fixing it in the database, ' .
                        'but it should be something else. Please re-run this action after you fix this problem.'
                    );

                    $netCidr = intval($netCidr);
                }

                $computeMember->update([
                    'local_ip_addr' =>  $localIp . '/' . $netCidr
                ]);

                $computeMember = $computeMember->fresh();
            }

            $netInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('device', $interfaceDetail['device'])
                ->where('vlan', $interfaceDetail['VLAN'])
                ->where('iaas_compute_member_id', $computeMember->id)
                ->first();

            Log::info('[ComputeMemberService@updateInterfaceInformation] Syncing interface: '
                . $interfaceDetail['device'] . ' for compute member: ' . $computeMember->name
                . ' with details: ' . print_r($data, true));

            if($netInterface)
                $netInterface->update($data);
            else {
                ComputeMemberNetworkInterfaces::createAsAdministrator($data);
            }
        }

        return $computeMember->fresh();
    }

    public static function updateNetworkInformation(ComputeMembers $computeMember) : ComputeMembers
    {
        logger()->info('[ComputeMemberService@updateNetworkInformation] We are starting to sync'
            . ' networks on the compute member. These networks can be VLANS, VxLANS.');

        $command = 'xe network-list';
        $result = self::performCommand($command, $computeMember);
        $bridges = self::parseListResult($result['output']);

        foreach ($bridges as $bridge) {
            $command = 'xe network-param-list uuid=' . $bridge['uuid'];
            $result = self::performCommand($command, $computeMember);
            $details = self::parseListResult($result['output']);
            $details = $details[0];

            //  If this is the host internal management network, then we dont need to create a network for this.
            if($details['name-label'] == 'Host internal management network')
                continue;

            $pif = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $details['PIF-uuids'])
                ->first();

            if(!$pif) {
                //  Technically this should not be happening but I added this here just in case!
                Log::error('[ComputeMemberService@updateNetworkInformation] Cannot find the PIF for the network: '
                    . $details['name-label'] . ' on compute member: ' . $computeMember->name);

                continue;
            }

            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $pif->vlan)
                ->first();

            if($network)
                continue;

            $data = [
                'name'          =>  $details['name-label'],
                'is_management' =>  false,
                'is_vpn'        =>  false,
                'is_public'     =>  false,
                'is_connected'  =>  $details['managed'],
                'mtu'           =>  $details['MTU'],
                'hypervisor_uuid'   =>  $details['uuid'],
                'vlan'          =>  $pif->vlan,
                'vxlan'         =>  0,
                'bandwidth'     =>  Str::remove('Mbit/s', $pif->hypervisor_data['speed']),
                'iaas_network_pool_id'  => ComputeMembersService::getNetworkPool($computeMember)->id,
                'iaas_cloud_node_id'    =>  ComputeMembersService::getCloudNode($computeMember)->id,
                'iam_account_id'    =>  $computeMember->iam_account_id,
                'iam_user_id'       =>  $computeMember->iam_user_id
            ];

            Log::info('[ComputeMemberService@updateNetworkInformation] Adding network: '
                . $details['name-label'] . ' on compute member: ' . $computeMember->name
                . ' with details: ' . print_r($data, true));

            NetworksService::create($data);
        }

        return $computeMember->fresh();
    }

    public static function updateStorageVolumes(ComputeMembers $computeMember) : ComputeMembers
    {
        $command = 'xe sr-list';
        $result = self::performCommand($command, $computeMember);

        if($result == null) {
            //  This can be null for couple of reasons. We need to check if the compute member is available.
            Log::error('[ComputeMemberService@updateStorageVolumes] Cannot get the storage volumes of the compute member: '
                . $computeMember->name . ' because the compute member is not available.');

            return $computeMember;
        }

        $volumes = self::parseListResult($result['output']);

        foreach ($volumes as $volume) {
            /*
             * We will look at the array and try to understand if we have a local storage. If we have a local storage
             * we should add this compute member as storage member also. Because we need to register the volume as
             * local storage volume which. And put this storage member to Local Storage Pool.
             */
            if($volume['type'] == 'lvm' || $volume['type'] == 'ext' || $volume['type'] == 'ext3' || $volume['type'] == 'ext4') {
                self::updateLocalStorageVolume($volume, $computeMember);
            }

            if($volume['type'] == 'udev' || $volume['type'] == 'iso') {
                self::updateUserDeviceAndDvdRom($volume, $computeMember);
            }

            if($volume['type'] == 'nfs' || $volume['type'] == 'nfs_vhd') {
                self::updateNfsStorageVolume($volume, $computeMember);
            }
        }

        return $computeMember->fresh();
    }

    public static function getListOfVirtualMachines(ComputeMembers $computeMember) : array
    {
        $command = 'xe vm-list';
        $result = self::performCommand($command, $computeMember);
        $vms = self::parseListResult($result['output']);

        return $vms;
    }

    public static function getListOfDisksOnVolume(ComputeMembers $computeMember, StorageVolumes $volume) : array
    {
        $command = 'xe vdi-list sr-uuid=' . $volume->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $disks = self::parseListResult($result['output']);

        return $disks;
    }

    public static function getVirtualMachineByUuid(ComputeMembers $computeMember, $uuid) : ?array
    {
        $command = 'xe vm-param-list uuid=' . $uuid;
        $result = self::performCommand($command, $computeMember);
        $vm = self::parseListResult($result['output']);

        return $vm;
    }

    public static function renameVirtualMachine(ComputeMembers $computeMembers, VirtualMachines $vm) : bool
    {
        Log::info('[VirtualMachineService@rename] Renaming VM: ' . $vm->hypervisor_uuid);

        $command = 'xe vm-param-set uuid=' . $vm->hypervisor_uuid . ' name-label="' . $vm->uuid . '"';
        $result = self::performCommand($command, $computeMembers);
        $result = self::parseListResult($result['output']);

        $vmParams = self::getVirtualMachineByUuid($computeMembers, $vm->hypervisor_uuid);

        if($vmParams[0]['name-label'] == $vm->uuid)
            return true;

        return false;
    }

    public static function getVDIofVms(ComputeMembers $computeMembers, VirtualMachines $vm) : array
    {
        $command = 'xe vbd-list vm-uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMembers);
        $vdis = self::parseListResult($result['output']);

        return $vdis;
    }

    public static function getVDIInfo(ComputeMembers $computeMembers, VirtualMachines $vm) : array
    {
        $command = 'xe vdi-param-list uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMembers);
        $vdi = self::parseListResult($result['output']);

        return [];
    }

    public static function updateVirtualMachines(ComputeMembers $computeMember) : ComputeMembers
    {
        return $computeMember->fresh();
    }

    private static function updateNfsStorageVolume($volume, $computeMember) : ComputeMemberStorageVolumes
    {
        $storageVolume = self::getStorageVolumeByHypervisorUuid($volume['uuid']);

        $command = 'xe pbd-list sr-uuid=' . $volume['uuid'] . ' params=uuid';
        $result = self::performCommand($command, $computeMember);
        $pbd = self::parseListResult($result['output']);

        $command = 'xe pbd-param-list uuid=' . $pbd[0]['uuid'];
        $result = self::performCommand($command, $computeMember);
        $pbdParams = self::parseListResult($result['output']);

        $command = 'xe sr-param-list uuid=' . $volume['uuid'];
        $result = self::performCommand($command, $computeMember);
        $volumeParamList = self::parseListResult($result['output']);

        if(array_key_exists(0, $volumeParamList))
            $volumeParamList = $volumeParamList[0];

        if(array_key_exists(0, $pbdParams))
            $pbdParams = $pbdParams[0];

        $data = [
            'name'   =>  $volume['name-label'],
            'description'   =>  $volume['name-description'],
            'hypervisor_uuid'  => $volume['uuid'],
            'hypervisor_data'  => $volumeParamList,
            'block_device_data' =>  $pbdParams,
            'iam_user_id'       =>  $computeMember->iam_user_id,
            'iam_account_id'    =>  $computeMember->iam_account_id,
            'iaas_compute_member_id'    =>  $computeMember->id
        ];

        if(!$storageVolume) {
            $storageVolume = ComputeMemberStorageVolumesService::create($data);
        } else {
            $storageVolume->update($data);
        }

        //  Here we are updating the shared storage member. If we dont have a record about the shared storage member
        //  then we will create one here.
        StorageVolumeXenService::updateSharedStorage($storageVolume);

        return $storageVolume;
    }

    private static function updateUserDeviceAndDvdRom($volume, $computeMember) : ComputeMemberStorageVolumes
    {
        $storageVolume = self::getStorageVolumeByHypervisorUuid($volume['uuid']);

        $command = 'xe pbd-list sr-uuid=' . $volume['uuid'] . ' params=uuid';
        $result = self::performCommand($command, $computeMember);
        $pbd = self::parseListResult($result['output']);

        $command = 'xe pbd-param-list uuid=' . $pbd[0]['uuid'];
        $result = self::performCommand($command, $computeMember);
        $pbdParams = self::parseListResult($result['output']);

        $data = [
            'name'   =>  $volume['name-label'],
            'description'   =>  $volume['name-description'],
            'hypervisor_uuid'  => $volume['uuid'],
            'hypervisor_data'  => $volume,
            'block_device_data' =>  $pbdParams,
            'iam_user_id'       =>  $computeMember->iam_user_id,
            'iam_account_id'    =>  $computeMember->iam_account_id
        ];

        if(!$storageVolume) {
            $storageVolume = ComputeMemberStorageVolumesService::create($data);
        } else {
            $storageVolume->update($data);
        }

        return $storageVolume;
    }

    private static function updateLocalStorageVolume($volume, $computeMember) : ComputeMemberStorageVolumes
    {
        //  Check if the compute member is also a storage member
        $storageMember = StorageMembersService::getStorageMemberOfComputeMember($computeMember);

        if(!$storageMember)
            $storageMember = StorageMemberXenService::createStorageMemberFromComputeMember($computeMember);

        /**
         * Stage 1: We are creating the iaas_compute_member_storage_volue
         */

        /**
         * Here we will check if the volume is already registered as a storage volume. If not we will register it.
         * But we also should register the volume to the storage member.
         */
        $storageVolume = self::getStorageVolumeByHypervisorUuid($volume['uuid']);

        //  We need to save PBD data too.

        $command = 'xe pbd-list sr-uuid=' . $volume['uuid'] . ' params=uuid';
        $result = self::performCommand($command, $computeMember);
        $pbd = self::parseListResult($result['output']);

        $command = 'xe pbd-param-list uuid=' . $pbd[0]['uuid'];
        $result = self::performCommand($command, $computeMember);
        $pbdParams = self::parseListResult($result['output']);

        $data = [
            'name'   =>  $volume['name-label'],
            'description'   =>  $volume['name-description'],
            'hypervisor_uuid'  => $volume['uuid'],
            'hypervisor_data'  => $volume,
            'block_device_data' =>  $pbdParams,
            'iam_user_id'       =>  $computeMember->iam_user_id,
            'iam_account_id'    =>  $computeMember->iam_account_id,
            'iaas_compute_member_id'    =>  $computeMember->id,
            'is_local_storage'  =>  true,
        ];

        if(!$storageVolume) {
            $storageVolume = ComputeMemberStorageVolumesService::create($data);
        } else {
            $storageVolume->update($data);
        }

        //  Now we need to check if the storage volume is already registered to the storage member
        /**
         * Stage 2: We are creating the iaas_storage_volume
         */

        $storageMemberVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $volume['uuid'])
            ->first();

        $storageMemberPool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('storage_pool_type', 'local')
            ->where('id', $storageMember->iaas_storage_pool_id)
            ->first();

        if(!$storageMemberVolume) {
            $storageMemberVolume = StorageVolumesService::create([
                'hypervisor_uuid'   =>  $volume['uuid'],
                'name'   =>  $volume['name-label'],
                'description'   =>  $volume['name-description'],
                'hypervisor_data'   =>  $volume,
                'iaas_storage_member_id'    =>  $storageMember->id,
                'iaas_storage_pool_id'      =>  $storageMemberPool->id
            ]);
        }

        $storageVolume->update([
            'iaas_storage_volume_id'    =>  $storageMemberVolume->id,
            'iaas_storage_member_id'    =>  $storageMember->id,
            'iaas_storage_pool_id'      =>  $storageMemberPool->id
        ]);

        return $storageVolume->fresh();
    }

    public static function getNetworkInterfaceFromVlan(ComputeMembers $computeMember, $vlan) : ComputeMemberNetworkInterfaces {
        $interface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('vlan', $vlan)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->first();

        /**
         * If we have interface but not in database this means that we didnt make the sync properly. We need to
         * do the sync again.
         */
        if(!$interface) {
            self::updateInterfaceInformation($computeMember);

            $interface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $vlan)
                ->where('iaas_compute_member_id', $computeMember->id)
                ->first();

            if(!$interface) {
                /**
                 *  This means that we still dont have the Interface, then there
                 * should be a serious problem that we need to take a look at.
                 */
                Log::error(__METHOD__ . ' | I cannot find the related Network Interface even ' .
                    'though I am sure that it is created and resyned again!!!');

                throw new SynchronizationException('I cannot find the related Network Interface even ' .
                    'though I am sure that it is created and resyned again!!!');
            }

            return $interface;
        }

        return $interface;
    }

    public static function createNetwork(ComputeMembers $computeMember, Networks $network) : ComputeMemberNetworkInterfaces
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[NetworkService@createNetworkOnComputeMember] Creating network : ' . $network->name
                . ' on compute member: ' . $computeMember->name);

        $isNetworkExists = self::isNetworkExists($computeMember, $network);

        if($isNetworkExists) {
            return self::getNetworkInterfaceFromVlan($computeMember, $network->vlan);
        }

        $defaultPIF = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('is_default', true)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->first();

        if(!$defaultPIF) {
            Log::error(__METHOD__ . ' | Cannot find the default interface on the compute member. Maybe' .
                ' we dont have ?');

            throw new NetworkNotInPoolException('Cannot find the default interface on the compute member. ' .
                'Maybe we dont have ?');
        }

        //  If we are here this means that we could not find the interface, now we need to create
        $command = 'xe network-create name-label=' . $network->name;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        $command = 'xe vlan-create network-uuid=' . $result . ' pif-uuid=' . $defaultPIF->hypervisor_uuid . ' vlan=' . $network->vlan;
        $result = self::performCommand($command, $computeMember);

        $result = $result['output'];

        self::updateInterfaceInformation($computeMember);

        return self::getNetworkInterfaceFromVlan($computeMember, $network->vlan);
    }

    public static function getStorageVolumeByHypervisorUuid($uuid) : ? ComputeMemberStorageVolumes
    {
        return ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $uuid)
            ->first();
    }

    public static function mountRepository(ComputeMembers $computeMember, Repositories $repositories) : array {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Starting to mount default repo ' .
                'of compute member: ' . $computeMember->name);

        $computeMemberPath = '/mnt/plusclouds-repo/' . $repositories->uuid;
        $createDirectoryCommand = 'mkdir -p ' . $computeMemberPath;

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Creating the directory with command; ' .
                $createDirectoryCommand);

        $result = self::performCommand($createDirectoryCommand, $computeMember);
        $result = $result['output'];

        $cloudNode = ComputeMembersService::getCloudNode($computeMember);

        $mountRepoCommand = 'mount -t nfs ' . $repositories->local_ip_addr . ':' . $repositories->vm_path . ' ' . $computeMemberPath;

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Mounting the default backup ' .
                'repo with command: ' . $mountRepoCommand);

        $result = self::performCommand($mountRepoCommand, $computeMember);

        return $result;
    }

    public static function mountDefaultBackupRepository(ComputeMembers $computeMember) : array {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Starting to mount default backup repo ' .
                'of compute member: ' . $computeMember->name);

        $computeMemberPath = '/mnt/plusclouds-backup-repo';
        $createDirectoryCommand = 'mkdir -p ' . $computeMemberPath;

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Creating the directory with command; ' .
                $createDirectoryCommand);

        $result = self::performCommand($createDirectoryCommand, $computeMember);
        $result = $result['output'];

        $cloudNode = ComputeMembersService::getCloudNode($computeMember);

        $mountRepoCommand = 'mount -t nfs ' . $cloudNode->default_backup_path . ' /mnt/plusclouds-backup-repo';

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Mounting the default backup ' .
                'repo with command: ' . $mountRepoCommand);

        $result = self::performCommand($mountRepoCommand, $computeMember);

        return $result;
    }

    public static function mountVmRepository(ComputeMembers $computeMember, Repositories $repo) : bool
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Starting to mount the repository: ' .
                $repo->name . ' to the compute member: ' . $computeMember->name);

        $computeMemberPath = '/mnt/plusclouds-repo/' . $repo->uuid;
        $createDirectoryCommand = 'mkdir -p ' . $computeMemberPath;

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Creating the directory with command; ' .
                $createDirectoryCommand);

        $result = self::performCommand($createDirectoryCommand, $computeMember);
        $result = $result['output'];

        $mountRepoCommand = 'mount -t nfs ' . $repo->local_ip_addr . ':' . $repo->vm_path . ' ' . $computeMemberPath;

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Mounting the repository with command; ' .
                $mountRepoCommand);

        $result = self::performCommand($mountRepoCommand, $computeMember);
        $result = $result['output'];

        $result = self::performCommand('ls ' . $computeMemberPath . '/hash.txt', $computeMember);
        $result = $result['output'];

        if($result == $computeMemberPath . '/hash.txt')
            return true;

        return false;
    }

    public static function unmountVmRepository(ComputeMembers $computeMember, Repositories $repo) : bool
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Starting to mount the repository: ' .
                $repo->name . ' to the compute member: ' . $computeMember->name);

        $computeMemberPath = '/mnt/plusclouds-repo/' . $repo->uuid;

        $umountRepoCommand = 'umount ' . $computeMemberPath;

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Unmounting the repository with command; ' .
                $umountRepoCommand);

        $result = self::performCommand($umountRepoCommand, $computeMember);

        $checkCommand = 'ls ' . $computeMemberPath . '/';
        $result = self::performCommand($checkCommand, $computeMember);
        $result = $result['output'];

        if(strlen($result) > 0) {
            Log::error('[ComputeMembersXenService@mountVmRepo] The repository is not unmounted properly. ' .
                'The directory is not empty. ');

            return false;
        }

        return true;
    }

    public static function importVirtualMachine(
        ComputeMembers $computeMember,
        StorageVolumes $volume,
        RepositoryImages $image
    )
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Starting to import the repository: ' .
                $image->name . ' to the compute member: ' . $computeMember->name);

        //  First we need to check if the image exists. If image does not exists we will trigger an event so that
        // we can catch that event later and fix the error.

        $repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $image->iaas_repository_id)
            ->first();

        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Checking if the related' .
                ' image (' . $image->name . '/' . $image->uuid . ') is available in the' .
                ' repository: ' . $repository->name . '/' . $repository->uuid);

        $command = 'ls /mnt/plusclouds-repo/' . $repository->uuid . '/' . $image->filename;
        $result = self::performCommand($command, $computeMember);

        if(Str::contains($result['error'], "ls: cannot access")) {
            Events::fire('image-lost:NextDeveloper\Iaas\RepositoryImages', $image);

            if(config('leo.debug.iaas.compute_members'))
                Log::error('[ComputeMembersXenService@mountVmRepo] Unfortunately the image: ' .
                    $image->name . ' is not available in the repository: ' . $repository->name . '. ' .
                    'We triggered an event to fix this issue.');

            throw new CannotImportException('I cannot find the given machine image. Seems' .
                ' like it is missing. ' .
                'I also fired an image-lost event, to syncronize the image service again.' .
                ' Next time you try to import a machine you may not find the' .
                ' same machine image in the database.');
        }

        $mountedVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->where('iaas_storage_volume_id', $volume->id)
            ->first();

        $command = 'xe vm-import ';
        $command .= 'filename=/mnt/plusclouds-repo/' . $repository->uuid . '/' . $image->filename;
        $command .= ' sr-uuid='.$volume->hypervisor_uuid;

        $result = self::performCommand($command, $computeMember);

        return $result['output'];
    }

    public static function mountIsoRepository(ComputeMembers $computeMember, Repositories $repo) {
        if(config('leo.debug.iaas.compute_members'))
            Log::info('[ComputeMembersXenService@mountVmRepo] Starting to mount the repository: ' .
                $repo->name . ' to the compute member: ' . $computeMember->name);

        $srList = self::performCommand('xe sr-list', $computeMember);
        $srList = self::parseListResult($srList['output']);

        foreach ($srList as $sr) {
            if($sr['name-label'] == 'ISO on ' . $repo->name) {
                Log::info('[ComputeMembersXenService@mountIsoRepo] The ISO' .
                    ' repository is already mounted on the compute member: ' . $computeMember->name);
                return true;
            }
        }

        $command = 'xe sr-create content-type=iso type=iso' .
            ' name-label="ISO on ' . $repo->name . '" device-config:nfsversion=4 device-config:type=nfs_iso' .
            ' device-config:location=' . $repo->local_ip_addr . ':' . $repo->iso_path .
            ' shared=true';

        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        $srList = self::performCommand('xe sr-list', $computeMember);
        $srList = self::parseListResult($srList['output']);

        foreach ($srList as $sr) {
            if($sr['name-label'] == 'ISO on ' . $repo->name) {
                Log::info('[ComputeMembersXenService@mountIsoRepo] The ISO' .
                    ' repository is already mounted on the compute member: ' . $computeMember->name);
                return true;
            }
        }

        return false;
    }

    public static function isNetworkExists(ComputeMembers $computeMember, Networks $network) : bool {
        if(config('leo.debug.iaas.compute_members'))
            Log::info(__METHOD__ . ' | Checking if the network exstis: ' .
                $network->name . ' in the compute member: ' . $computeMember->name);

        $srList = self::performCommand('xe pif-list VLAN=' . $network->vlan, $computeMember);

        if($srList['output'] == '')
            return false;

        return true;
    }

    public static function performCommand($command, ComputeMembers $computeMember) : ?array
    {
        try {
            if($computeMember->is_management_agent_available == true) {
                return $computeMember->performAgentCommand($command);
            } else {
                return $computeMember->performSSHCommand($command);
            }
        } catch (\Exception $e) {
            Log::error('[ComputeMembersXenService@performCommand] There is an error while performing the command: ' .
                $command . ' on the compute member: ' . $computeMember->name . ' with error: ' . $e->getMessage());

            StateHelper::setState(
                $computeMember,
                'netconf_netmask_problem',
                'has_errors',
                StateHelper::STATE_ERROR,
                'There is an error while performing the command: ' . $command . ' on the compute member: ' .
                $computeMember->name . ' with error: ' . $e->getMessage()
            );

            return null;
        }
    }
}
