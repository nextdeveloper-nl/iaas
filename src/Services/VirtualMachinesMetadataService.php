<?php

namespace NextDeveloper\IAAS\Services;

use App\Helpers\Http\ResponseHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Exceptions\NotFoundException;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotCreateVirtualMachine;
use NextDeveloper\IAAS\Exceptions\CannotUpdateResourcesException;
use NextDeveloper\IAAS\Helpers\IaasHelper;
use NextDeveloper\IAAS\Helpers\ResourceCalculationHelper;
use NextDeveloper\IAAS\ResourceLimiters\SimpleLimiter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for VirtualMachines
 *
 * Class VirtualMachinesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachinesMetadataService extends AbstractVirtualMachinesService
{
    public static function getMetadata(VirtualMachines $vm) : array
    {
        $vdis = VirtualMachinesService::getVirtualDiskImages($vm);
        $vifs = VirtualMachinesService::getVirtualNetworkCards($vm);

        $diskConfiguration = [];

        foreach ($vdis as $vdi) {
            $diskConfiguration[] = [
                'disk_type'     => $vdi->disk_type,
                'device_number' => $vdi->device_number,
                'total_disk'    => $vdi->size,
            ];
        }

        $vifConfiguration = [];

        foreach ($vifs as $vif) {
            $data = [
                'device_number' => $vif->device_number,
                'mac_addr'      => $vif->mac_addr,
//                'network'       => [
//                    'ip_addr'           => $vif->ip_addr,
//                    'ip_range_start'    => $vif->ip_range_start,
//                    'ip_range_end'      => $vif->ip_range_end,
//                    'gateway'           => $vif->gateway,
//                    'subnet'            => $vif->subnet,
//                    'netmask'           => $vif->netmask,
//                    'network'           => $vif->network,
//                    'dhcp_server'       => $vif->dhcp_server,
//                    'dns_nameservers'   => $vif->dns_nameservers,
//                    'mtu'               => $vif->mtu,
//                ],
            ];

            if($vif->ipList) {
                $data['ipList'] = [
                    'data' => $vif->ipList->map(function ($ip) {
                        return [
                            'id'            => $ip->id,
                            'ip_addr'      => $ip->ip_addr,
                            'version'      => $ip->version,
                            'is_reachable' => $ip->is_reachable
                        ];
                    }),
                ];
            }

            $vifConfiguration[] = $data;
        }

        $computePool = VirtualMachinesService::getComputePool($vm);

        $computePoolArray = [
            'id' => $computePool->uuid,
            'name' => $computePool->name,
            'pool_type' => $computePool->pool_type,
            'hypervisor_type' => $computePool->hypervisor_type,
            'hypervisor_version' => $computePool->hypervisor_version,
        ];

        $computeMember = VirtualMachinesService::getComputeMember($vm);
        $computeMemberArray = [];

        if($computeMember) {
            $computeMemberArray = [
                'id' => $computeMember->uuid,
                'name' => $computeMember->name,
                'ip_addr' => $computeMember->ip_addr,
                'local_ip_addr' => $computeMember->local_ip_addr,
                'is_behind_firewall' => $computeMember->is_behind_firewall,
                'username' => $computeMember->username,
            ];
        }

        $cloudNode = VirtualMachinesService::getCloudPool($vm);
        $cloudPoolArray = [
            'id' => $cloudNode->uuid,
            'name' => $cloudNode->name,
            'location' => $cloudNode->location,
            'provider' => $cloudNode->provider,
            'region' => $cloudNode->region,
        ];

        return [
            'hostname' => $vm->hostname,
            'username' => $vm->username,
            'password' => $vm->password,
            'virtual_machine_id' => $vm->id_ref,
            'virtual_disks' => $diskConfiguration,
            'virtual_network_cards' => $vifConfiguration,
            'service_roles' => [
                //  Here will be roles of the server
            ],
            'compute_member' => $computeMemberArray,
            'compute_pool' => $computePoolArray,
            'cloud_node' => $cloudPoolArray,
            'ssh_keys' => [],
        ];
    }
}
