<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractNetworksService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * This class is responsible from managing the data for Networks
 *
 * Class NetworksService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class NetworksService extends AbstractNetworksService
{
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create($data) : Networks {
        if(!array_key_exists('vlan', $data)) {
            $networkPool = NetworkPoolsService::getNetworkPool($data['iaas_network_pool_id']);
            $data['vlan'] = NetworkPoolsService::getNextAvailableVlan($networkPool);
        }

        if(array_key_exists('dns_nameservers', $data)) {
            if(!is_array($data['dns_nameservers'])) {
                $data['dns_nameservers'] = explode(',', $data['dns_nameservers']);
            }
        }

        if(!array_key_exists('vxlan', $data)) {
            $data['vxlan'] = -1;
        }

        return parent::create($data);
    }

    public static function getCloudNode(Networks $network)
    {
        return CloudNodes::withoutGlobalScope(AuthorizationScope::class)->where('id', $network->iaas_cloud_node_id)
            ->first();
    }

    public static function getPublicNetwork(CloudNodes $node) : Networks
    {
        $cloudPool = NetworkPools::withoutGlobalScope(AuthorizationScope::class)->where('iaas_cloud_node_id', $node->id)
            ->first();

        $network = Networks::where('iaas_network_pool_id', $cloudPool->id)
            ->where('is_public', true)
            ->where('is_dmz', true)
            ->first();

        if(!$network) {
            throw new ResourceNotFoundException('Cannot find public network with DMZ option. Please consult to your cloud provider.');
        }

        return $network;
    }
}
