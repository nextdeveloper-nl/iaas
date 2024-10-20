<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractCloudNodesService;

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
}
