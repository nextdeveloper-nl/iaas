<?php

namespace NextDeveloper\IAAS\Services\DHCP;

use IPv4\SubnetCalculator;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\Intelligence\Database\Models\Ips;

class IscDhcpServices
{
    public static function generateReservedHostsConfig(DhcpServers $dhcpServers, Networks $networks) {

    }

    public static function generateServerConfiguration(DhcpServers $dhcpServers) {
        $config = 'ddns-update-style none;' . PHP_EOL;
        $config .= 'default-lease-time 172800;' . PHP_EOL;
        $config .= 'option domain-name-servers 8.8.4.4,4.4.2.2;' . PHP_EOL;
        $config .= 'max-lease-time 172850;' . PHP_EOL;
        $config .= 'authoritative;' . PHP_EOL;
        $config .= 'log-facility local7;' . PHP_EOL;
        $config .= 'include "/etc/dhcp/mac.conf";' . PHP_EOL;
        $config .= PHP_EOL;

        $networks = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_dhcp_server_id', $dhcpServers->id)
            ->get();

        foreach ($networks as $network) {
            $subnet = explode('/', $network->cidr);
            $subnetCalculator = new SubnetCalculator($subnet[0], $subnet[1]);
            $range = $subnetCalculator->getIPAddressRange();

            $config .= 'subnet ' . $range[0] . ' netmask ' . $subnetCalculator->getSubnetMask() . ' {' . PHP_EOL;
            $config .= '    range ' . explode('/', $network->ip_addr_range_start)[0] . ' ' . explode('/', $network->ip_addr_range_end)[0] . ';' . PHP_EOL;
            $config .= '    option broadcast-address ' . $range[1] . ';' . PHP_EOL;
            $config .= '    option routers ' . $subnetCalculator->getMinHost() . ';' . PHP_EOL;
            $config .= '    option domain-name-servers 8.8.4.4, 4.4.2.2;' . PHP_EOL;
            $config .= '    option domain-name "";' . PHP_EOL;
            $config .= '}' . PHP_EOL;

            $ips = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->withoutGlobalScope(LimitScope::class)
                ->where('iaas_network_id', $network->id)
                ->get();

            foreach ($ips as $ip) {
                $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('id', $ip->iaas_virtual_network_card_id)
                    ->first();

                $config .= 'host ' . md5($vnc->uuid . $ip->uuid) . ' { hardware ethernet ' . $vnc->mac_addr . '; fixed-address ' . $ip->ip_addr . '; }' . PHP_EOL;
            }
        }

        return $config;
    }

    public static function generateSubnetsConfig(DhcpServers $dhcpServers) {

    }
}
