<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Exceptions\CannotFindAvailableResourceException;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractNetworkPoolsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for NetworkPools
 *
 * Class NetworkPoolsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class NetworkPoolsService extends AbstractNetworkPoolsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getNetworkPool(Networks $network) : NetworkPools
    {
        $networkPools = NetworkPools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $network->iaas_network_pool_id)
            ->first();

        return $networkPools;
    }

    public static function getNextAvailableVlan(NetworkPools $networkPool) : int
    {
        $vlans = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_network_pool_id', $networkPool->id)
            ->orderBy('vlan', 'asc')
            ->pluck('vlan');

        $vlanStart = $networkPool->vlan_start;
        $vlanEnd = $networkPool->vlan_end;

        for($i = $vlanStart; $i <= $vlanEnd; $i++) {
            if(!$vlans->contains($i)) {
                return $i;
            }
        }

        throw new CannotFindAvailableResourceException('There is no available vlan in the network pool');
    }
}
