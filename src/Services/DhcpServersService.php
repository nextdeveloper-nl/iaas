<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractDhcpServersService;
use NextDeveloper\IAAS\Services\DHCP\IscDhcpServices;

/**
 * This class is responsible from managing the data for DhcpServers
 *
 * Class DhcpServersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class DhcpServersService extends AbstractDhcpServersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getConfiguration(DhcpServers $server)
    {
        switch ($server->server_type) {
            case 'isc-linux':
            case 'isc-docker':
            case 'isc-linux-http':
                return IscDhcpServices::generateServerConfiguration($server);
                break;
            default:
                return [];
        }
    }
}
