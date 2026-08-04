<?php

namespace NextDeveloper\IAAS\Services;

use IPv4\SubnetCalculator;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIpAddressesService;
use NextDeveloper\IAM\Database\Models\Accounts;
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

    /**
     * Creates an IpAddresses record for an ip that was seen live on a switch's arp table but
     * has no record in our database at all. Ownership is resolved from the mac address: if it
     * matches a VirtualNetworkCards row, the ip is attached to that card and to the account
     * (and that account's owning user) the card belongs to - the same resolution
     * NetworkMembers\UpdateIpsWithArp already used. If the mac doesn't match any of our cards
     * the record is still created, just unowned with only the mac on file, since an ip we cant
     * attribute to anyone is exactly the kind of manually assigned address we want visibility
     * into.
     */
    public static function createFromArpEntry(string $ip, string $mac, ?int $networkId) : IpAddresses
    {
        $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('mac_addr', $mac)
            ->first();

        $owner = $vnc
            ? Accounts::withoutGlobalScope(AuthorizationScope::class)->where('id', $vnc->iam_account_id)->first()
            : null;

        return self::create([
            'ip_addr'                       =>  $ip,
            'iaas_network_id'                =>  $networkId,
            'iaas_virtual_network_card_id'   =>  $vnc ? $vnc->id : null,
            'custom_mac_addr'                =>  $vnc ? null : $mac,
            'iam_account_id'                 =>  $owner ? $owner->id : null,
            'iam_user_id'                    =>  $owner ? $owner->iam_user_id : null,
        ]);
    }
}
