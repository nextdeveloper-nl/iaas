<?php

namespace NextDeveloper\IAAS\Services\DHCP;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use IPv4\SubnetCalculator;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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

            $macList = [];

            foreach ($ips as $ip) {
                $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('id', $ip->iaas_virtual_network_card_id)
                    ->first();

                $ipAddr = $ip->ip_addr;

                if(Str::contains($ipAddr, '/')) {
                    $ipAddr = explode('/', $ipAddr);
                    $ipAddr = $ipAddr[0];
                }

                if($vnc) {
                    if(in_array($vnc->mac_addr, $macList)) {
                        Log::warning(__METHOD__ . ' | I found more that one same mac address in the' .
                            ' configuration so I am taking only the first one. Seems like there is a problem' .
                            ' with the configuration or records.');

                        continue;
                    }

                    if($vnc)
                        $config .= 'host ' . md5($vnc->uuid . $ip->uuid) . ' { hardware ethernet ' . $vnc->mac_addr . '; fixed-address ' . $ipAddr . '; }' . PHP_EOL;

                    $macList[] = $vnc->mac_addr;
                }

                if($ip->custom_mac_addr && !$vnc)
                    $config .= 'host CUSTOM-' . md5($ip->custom_mac_addr) . ' { hardware ethernet ' . $ip->custom_mac_addr . '; fixed-address ' . $ipAddr . '; }' . PHP_EOL;
            }
        }

        return $config;
    }

    public static function generateSubnetsConfig(DhcpServers $dhcpServers) {

    }
}
