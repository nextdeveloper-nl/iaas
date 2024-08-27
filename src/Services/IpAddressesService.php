<?php

namespace NextDeveloper\IAAS\Services;

use IPv4\SubnetCalculator;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIpAddressesService;

/**
 * This class is responsible from managing the data for IpAddresses
 *
 * Class IpAddressesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class IpAddressesService extends AbstractIpAddressesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getRandomAvailableIp(Networks $network)
    {
        $subnet = $network->cidr;
        $subnetCalculator = new SubnetCalculator($subnet[0], $subnet[1]);

        dd($subnetCalculator);
    }
}
