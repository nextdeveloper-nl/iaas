<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Database\Eloquent\Collection;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractCloudNodesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for CloudNodes
 *
 * Class CloudNodesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class CloudNodesService extends AbstractCloudNodesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function networkExists(CloudNodes $cloudNode, Networks $network) : bool {
        $networkPool = NetworkPoolsService::getNetworkPool($network);

        if($networkPool->iaas_cloud_node_id == $cloudNode->id)
            return true;

        return false;
    }

    public static function getSlugsOfNodes() : Collection {
        return CloudNodes::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->get();
    }

    public static function getComputePools(CloudNodes $node) : Collection {
        return ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $node->id)
            ->get();
    }

    public static function getRepositories(CloudNodes $cloudNode) : Collection
    {
        return Repositories::where('iaas_cloud_node_id', $cloudNode->id)
            ->get();
    }
}
