<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Networks;
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
}
