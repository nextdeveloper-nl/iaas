<?php

namespace NextDeveloper\IAAS\Services;

use IPv4\SubnetCalculator;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIpAddressesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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

    public static function getNextIpAvailable(Networks $network): string
    {
        $subnet = $network->cidr;

        if (!$subnet) {
            throw new ModelNotFoundException('We cannot for a CIDR information for this network. ' .
                'This is highly likely be an error.');
        }

        $subnet = explode('/', $subnet);

        $subnetCalculator = new SubnetCalculator($subnet[0], $subnet[1]);
        $range = $subnetCalculator->getAddressableHostRange();

        $minIp = $range[0];
        $maxIp = $range[1];

        $minIp = ip2long($minIp);
        $maxIp = ip2long($maxIp);

        $foundIp = null;

        //  Checking if this IP address exists on database
        for($i = $minIp + 1; $i < $maxIp; $i++) {
            //  Checking if this IP address exists on database
            $existingIp = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_network_id', $network->id)
                ->where('ip_addr', long2ip($i))
                ->first();

            if(!$existingIp) {
                $foundIp = long2ip($i);
                break;
            }
        }

        if(!$foundIp) {
            throw new \Exception('Unfortunately we cannot find an IP address for you in this network. All is full.');
        }

        return $foundIp;
    }
}
