<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractNetworksService;

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
}
