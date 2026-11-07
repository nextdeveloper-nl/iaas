<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Helpers\NetworkCalculationHelper;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\ComputeMemberStorageVolumesService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\StorageMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\StorageVolumeXenService;
use NextDeveloper\IAAS\Services\NetworksService;
use NextDeveloper\IAAS\Services\StorageMembersService;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Host sync (what the Initiate action runs) for compute members managed over the HTTPS agent.
 *
 * This mirrors ComputeMemberXenService::updateMemberInformation / updateInterfaceInformation /
 * updateNetworkInformation / updateStorageVolumes step by step and writes the same rows, but
 * reads from the agent's xenserver.inventory.snapshot instead of `xe` over SSH. The legacy class
 * is not touched. Every element the agent returns is {uuid, params, maps, sets}; AgentResultMapper
 * flattens it to the xe-style array the rest of IAAS already stores in hypervisor_data.
 *
 * Differences from the SSH path, on purpose:
 *  - hypervisor_data keeps xe's map and set fields as real arrays (SSH stores some of them as
 *    "k: v; k: v" strings). The scalar keys other code reads (IP, netmask, speed, power-state, ...)
 *    are identical.
 *  - No SSH, no fallback: any agent failure surfaces as XenAgentException.
 */
class ComputeMemberAgentSyncService
{
    /**
     * Sets of xe's own storage types that Initiate registers, same lists as the SSH path.
     */
    private const LOCAL_SR_TYPES  = ['lvm', 'ext', 'ext3', 'ext4'];
    private const DEVICE_SR_TYPES = ['udev', 'iso'];
    private const NFS_SR_TYPES    = ['nfs', 'nfs_vhd'];

    public static function client(ComputeMembers $computeMember): XenAgentClient
    {
        return new XenAgentClient($computeMember);
    }

    /**
     * One agent call with everything the sync needs (pool, hosts, pifs, networks, vlans, srs,
     * pbds). Each list element is flattened to the xe-style array.
     */
    public static function inventory(ComputeMembers $computeMember): array
    {
        $snapshot = self::client($computeMember)->call('xenserver.inventory.snapshot');

        $flat = ['pool' => $snapshot['pool'] ?? []];

        foreach (['hosts', 'pifs', 'networks', 'vlans', 'srs', 'pbds'] as $kind) {
            $flat[$kind] = array_map([self::class, 'flatten'], $snapshot[$kind] ?? []);
        }

        return $flat;
    }

    /**
     * {uuid, params, maps, sets} -> flat xe-style array, always carrying 'uuid'.
     */
    public static function flatten(array $element): array
    {
        $flat = AgentResultMapper::paramList($element);

        if (!isset($flat['uuid']) && isset($element['uuid'])) {
            $flat['uuid'] = (string) $element['uuid'];
        }

        return $flat;
    }

    // -- host --------------------------------------------------------------------------

    /**
     * Same result as the SSH "name/hostname/uptime/ram/cpu/model" update of the compute member.
     * The agent answers for its own host, so no hostname matching against a pool is needed.
     */
    public static function updateMemberInformation(ComputeMembers $computeMember): ComputeMembers
    {
        $client = self::client($computeMember);

        $hypervisor = self::flatten($client->call('xenserver.host.param_list'));
        $system     = $client->call('system.info');

        $cpuInformation      = $hypervisor['cpu_info'] ?? [];
        $softwareInformation = $hypervisor['software-version'] ?? [];

        Log::info('[ComputeMemberAgentSyncService@updateMemberInformation] Got the host: ' . ($hypervisor['name-label'] ?? '?'));

        $computeMember->update([
            'name'              =>  $hypervisor['name-label'],
            'hostname'          =>  $hypervisor['hostname'],
            'hypervisor_uuid'   =>  $hypervisor['uuid'],
            'hypervisor_data'   =>  $hypervisor,
            //  The SSH path stores `stat -c %Z /proc/` (the boot time as a timestamp); the
            //  agent reports the same thing as boot_time.
            'uptime'            =>  $system['boot_time'] ?? null,
            'total_ram'         =>  ceil($hypervisor['memory-total'] / 1024 / 1024 / 1024),
            'used_ram'          =>  ceil(($hypervisor['memory-total'] - $hypervisor['memory-free']) / 1024 / 1024 / 1024),
            'total_cpu'         =>  $cpuInformation['cpu_count'] ?? null,
            'total_socket'      =>  $cpuInformation['socket_count'] ?? null,
            'hypervisor_model'  =>  'XenServer ' . trim($softwareInformation['product_version_text_short'] ?? ''),
            'cpu_info'          =>  $cpuInformation,
            'overbooking_ratio' =>  $computeMember->overbooking_ratio == 0 ? 15 : $computeMember->overbooking_ratio,
        ]);

        return $computeMember->fresh();
    }

    public static function detectVersion(ComputeMembers $computeMember): string
    {
        $hypervisor = self::flatten(self::client($computeMember)->call('xenserver.host.param_list'));

        return 'XenServer ' . trim($hypervisor['software-version']['product_version_text_short'] ?? '');
    }

    public static function isPoolMember(ComputeMembers $computeMember): bool
    {
        $pool = self::client($computeMember)->call('xenserver.pool.info');

        return ($pool['is_pool_member'] ?? false) === true;
    }

    // -- network interfaces (PIFs) and VLANs ----------------------------------------------

    public static function updateInterfaceInformation(ComputeMembers $computeMember): ComputeMembers
    {
        $inventory = self::inventory($computeMember);

        foreach ($inventory['pifs'] as $interfaceDetail) {
            $data = [
                'device'            =>  $interfaceDetail['device'],
                'mac_addr'          =>  $interfaceDetail['MAC'],
                'vlan'              =>  $interfaceDetail['VLAN'] ?? 0,
                'mtu'               =>  $interfaceDetail['MTU'],
                'is_management'     =>  $interfaceDetail['management'] == 'true',
                'is_connected'      =>  $interfaceDetail['currently-attached'] == 'true',
                'is_default'        =>  $interfaceDetail['management'] == 'true',
                'is_bridge'         =>  false,
                'speed'             =>  $interfaceDetail['speed'],
                'hypervisor_data'   =>  $interfaceDetail,
                'hypervisor_uuid'   =>  $interfaceDetail['uuid'],
                'network_uuid'      =>  $interfaceDetail['network-uuid'],
                'network_name'      =>  $interfaceDetail['network-name-label'],
                'iaas_compute_member_id'    =>  $computeMember->id,
                'iam_account_id'    =>  $computeMember->iam_account_id,
                'iam_user_id'       =>  $computeMember->iam_user_id
            ];

            if ($data['is_management']) {
                $netCidr = NetworkCalculationHelper::mask2cidr($interfaceDetail['netmask']);
                $localIp = $interfaceDetail['IP'];

                if (intval($netCidr) != $netCidr) {
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

            if ($netInterface) {
                unset($data['iam_account_id'], $data['iam_user_id']);

                $netInterface->update($data);
            } else {
                //  Same as the SSH path: creating the interface row needs admin rights, so the
                //  current user is swapped for the system user for this one write.
                $netInterface = self::runAsAdmin(fn() => ComputeMemberNetworkInterfaces::create($data));
            }

            //  A tagged PIF also gets its VLAN record stored.
            if ($netInterface->vlan != 0 && $netInterface->vlan != -1) {
                self::runAsAdmin(fn() => self::updateVlanInformation($computeMember, $netInterface->vlan, $inventory['vlans']));
            }
        }

        return $computeMember->fresh();
    }

    /**
     * Stores the VLAN record (vlan_data) of the interface that carries this tag. The vlan list
     * comes from the same inventory snapshot, so no extra agent call is needed.
     */
    public static function updateVlanInformation(ComputeMembers $computeMember, $vlan, array $vlans): ?ComputeMemberNetworkInterfaces
    {
        $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('vlan', $vlan)
            ->where('iaas_compute_member_id', $computeMember->id)
            ->first();

        $details = collect($vlans)->first(fn($candidate) => (string) ($candidate['tag'] ?? null) === (string) $vlan);

        if (!$cmni || !$details) {
            //  The SSH path would fail on a missing VLAN here. A tagged PIF with no VLAN record
            //  is logged and skipped instead of aborting the whole sync.
            Log::warning('[ComputeMemberAgentSyncService@updateVlanInformation] No VLAN record for tag ' . $vlan .
                ' on compute member: ' . $computeMember->name);

            return $cmni;
        }

        $cmni->update(['vlan_data' => $details]);

        return $cmni->fresh();
    }

    // -- networks ------------------------------------------------------------------------

    public static function updateNetworkInformation(ComputeMembers $computeMember): ComputeMembers
    {
        $inventory = self::inventory($computeMember);

        foreach ($inventory['networks'] as $details) {
            //  The host internal management network is not a customer network.
            if ($details['name-label'] == 'Host internal management network') {
                continue;
            }

            //  The SSH path reads PIF-uuids as one string. The agent returns the set, so the
            //  first PIF is used (a bonded network lists the same PIFs).
            $pifUuid = $details['PIF-uuids'][0] ?? null;

            $pif = $pifUuid
                ? ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                    ->where('hypervisor_uuid', $pifUuid)
                    ->first()
                : null;

            if (!$pif) {
                Log::error('[ComputeMemberAgentSyncService@updateNetworkInformation] Cannot find the PIF for the network: '
                    . $details['name-label'] . ' on compute member: ' . $computeMember->name);

                continue;
            }

            //  Same rule as the SSH path: one network per VLAN id.
            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $pif->vlan)
                ->first();

            if ($network) {
                continue;
            }

            NetworksService::create([
                'name'              =>  $details['name-label'],
                'is_management'     =>  false,
                'is_vpn'            =>  false,
                'is_public'         =>  false,
                'is_connected'      =>  $details['managed'],
                'mtu'               =>  $details['MTU'],
                'hypervisor_uuid'   =>  $details['uuid'],
                'vlan'              =>  $pif->vlan,
                'vxlan'             =>  0,
                'bandwidth'         =>  trim(Str::remove('Mbit/s', $pif->hypervisor_data['speed'])),
                'iaas_network_pool_id'  =>  ComputeMembersService::getNetworkPool($computeMember)->id,
                'iaas_cloud_node_id'    =>  ComputeMembersService::getCloudNode($computeMember)->id,
                'iam_account_id'    =>  $computeMember->iam_account_id,
                'iam_user_id'       =>  $computeMember->iam_user_id
            ]);
        }

        return $computeMember->fresh();
    }

    // -- storage -------------------------------------------------------------------------

    public static function updateStorageVolumes(ComputeMembers $computeMember): ComputeMembers
    {
        $inventory = self::inventory($computeMember);

        foreach ($inventory['srs'] as $volume) {
            $pbd = collect($inventory['pbds'])->first(fn($candidate) => ($candidate['sr-uuid'] ?? null) === $volume['uuid']);

            if (in_array($volume['type'], self::LOCAL_SR_TYPES, true)) {
                self::updateLocalStorageVolume($volume, $pbd, $computeMember);
            }

            if (in_array($volume['type'], self::DEVICE_SR_TYPES, true)) {
                self::updateUserDeviceAndDvdRom($volume, $pbd, $computeMember);
            }

            if (in_array($volume['type'], self::NFS_SR_TYPES, true)) {
                self::updateNfsStorageVolume($volume, $pbd, $computeMember);
            }
        }

        return $computeMember->fresh();
    }

    private static function updateNfsStorageVolume(array $volume, ?array $pbd, ComputeMembers $computeMember): ComputeMemberStorageVolumes
    {
        $storageVolume = self::findStorageVolume($volume['uuid']);

        $data = [
            'name'              =>  $volume['name-label'],
            'description'       =>  $volume['name-description'],
            'hypervisor_uuid'   =>  $volume['uuid'],
            'hypervisor_data'   =>  $volume,
            'block_device_data' =>  $pbd ?? [],
            'iam_user_id'       =>  $computeMember->iam_user_id,
            'iam_account_id'    =>  $computeMember->iam_account_id,
            'iaas_compute_member_id'    =>  $computeMember->id
        ];

        if (!$storageVolume) {
            $storageVolume = ComputeMemberStorageVolumesService::create($data);
        } else {
            $storageVolume->update($data);
        }

        //  Creates or updates the shared storage member of this volume (no SSH involved).
        StorageVolumeXenService::updateSharedStorage($storageVolume);

        return $storageVolume;
    }

    private static function updateUserDeviceAndDvdRom(array $volume, ?array $pbd, ComputeMembers $computeMember): ComputeMemberStorageVolumes
    {
        $storageVolume = self::findStorageVolume($volume['uuid']);

        $data = [
            'name'              =>  $volume['name-label'],
            'description'       =>  $volume['name-description'],
            'hypervisor_uuid'   =>  $volume['uuid'],
            'hypervisor_data'   =>  $volume,
            //  Wrapped in a list like the SSH path, which stores parseListResult() output here.
            'block_device_data' =>  $pbd ? [$pbd] : [],
            'iam_user_id'       =>  $computeMember->iam_user_id,
            'iam_account_id'    =>  $computeMember->iam_account_id,
            'iaas_compute_member_id'    =>  $computeMember->id
        ];

        if (!$storageVolume) {
            $storageVolume = ComputeMemberStorageVolumesService::create($data);
        } else {
            $storageVolume->update($data);
        }

        return $storageVolume;
    }

    private static function updateLocalStorageVolume(array $volume, ?array $pbd, ComputeMembers $computeMember): ComputeMemberStorageVolumes
    {
        //  A compute member with local storage is also a storage member.
        $storageMember = StorageMembersService::getStorageMemberOfComputeMember($computeMember);

        if (!$storageMember) {
            $storageMember = StorageMemberXenService::createStorageMemberFromComputeMember($computeMember);
        }

        //  Stage 1: the compute member's own storage volume row.
        $storageVolume = self::findStorageVolume($volume['uuid']);

        $data = [
            'name'              =>  $volume['name-label'],
            'description'       =>  $volume['name-description'],
            'hypervisor_uuid'   =>  $volume['uuid'],
            'hypervisor_data'   =>  $volume,
            'block_device_data' =>  $pbd ? [$pbd] : [],
            'iam_user_id'       =>  $computeMember->iam_user_id,
            'iam_account_id'    =>  $computeMember->iam_account_id,
            'iaas_compute_member_id'    =>  $computeMember->id,
            'is_local_storage'  =>  true,
        ];

        if (!$storageVolume) {
            $storageVolume = ComputeMemberStorageVolumesService::create($data);
        } else {
            $storageVolume->update($data);
        }

        //  Stage 2: the storage member's volume. physical-size and physical-utilisation drive
        //  total_hdd/used_hdd, which compute the free_hdd generated column.
        $storageMemberVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $volume['uuid'])
            ->first();

        $storageMemberPool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('storage_pool_type', 'local')
            ->where('id', $storageMember->iaas_storage_pool_id)
            ->first();

        $totalHdd = !empty($volume['physical-size'])
            ? (int) ceil((int) $volume['physical-size'] / 1000 / 1000 / 1000)
            : 0;
        $usedHdd = !empty($volume['physical-utilisation'])
            ? (int) ceil((int) $volume['physical-utilisation'] / 1000 / 1000 / 1000)
            : 0;

        $volumeData = [
            'hypervisor_uuid'        => $volume['uuid'],
            'name'                   => $volume['name-label'],
            'description'            => $volume['name-description'],
            'hypervisor_data'        => $volume,
            'disk_physical_type'     => 'local',
            'is_storage'             => true,
            'total_hdd'              => $totalHdd,
            'used_hdd'               => $usedHdd,
            'iaas_storage_member_id' => $storageMember->id,
            'iaas_storage_pool_id'   => $storageMemberPool->id,
        ];

        if (!$storageMemberVolume) {
            $storageMemberVolume = StorageVolumesService::create($volumeData);
        } else {
            $storageMemberVolume->update($volumeData);
        }

        $storageVolume->update([
            'iaas_storage_volume_id'    =>  $storageMemberVolume->id,
            'iaas_storage_member_id'    =>  $storageMember->id,
            'iaas_storage_pool_id'      =>  $storageMemberPool->id
        ]);

        return $storageVolume->fresh();
    }

    /**
     * Runs $callback as the system user and always puts the previous user back, even when the
     * callback throws (otherwise a failed write would leave the whole process elevated).
     */
    private static function runAsAdmin(callable $callback): mixed
    {
        $user    = UserHelper::me();
        $account = $user ? UserHelper::currentAccount($user) : null;

        UserHelper::setAdminAsCurrentUser();

        try {
            return $callback();
        } finally {
            if ($user && $account) {
                UserHelper::setCurrentUserAndAccount($user, $account);
            }
        }
    }

    private static function findStorageVolume(string $uuid): ?ComputeMemberStorageVolumes
    {
        return ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $uuid)
            ->first();
    }
}
