<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractNetworkMembersService;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for NetworkMembers
 *
 * Class NetworkMembersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class NetworkMembersService extends AbstractNetworkMembersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * Connects to the given switch over SSH and reads the arp table of every vlan (or a single
     * one, if $vlan is given), looking for ip addresses that are being answered for on the wire
     * by more than one mac address, or by a mac address that does not match what our own
     * records say should own that ip. Both situations only happen when an ip has been assigned
     * manually outside of our provisioning system.
     *
     * This talks to the switch directly and does not depend on NetworkMembersInterfaces being
     * synced into the database: if $vlan is given we go straight to it, otherwise we ask the
     * switch itself which vlans exist ("show vlan brief"). NetworkMembersInterfaces is only
     * ever used here as a lightweight, unsaved value holder for the interface name.
     *
     * $onProgress, if given, is called with a human readable string after every meaningful
     * step, so callers can surface what is happening live (e.g. a console command) instead of
     * only finding out about the result after the whole scan is done.
     *
     * $verbose additionally reports every single ip found in the arp table through $onProgress,
     * not just the ones that turned out to be a collision - matched ips and ips that are on the
     * wire but not in our IpAddresses table at all are reported too, so this can be used to
     * actually debug a network instead of only alerting on confirmed collisions.
     *
     * @return array List of collisions found, each with ip / macs / reason / interface / network_id
     */
    public static function detectIpCollisions(NetworkMembers $switch, ?int $vlan = null, ?callable $onProgress = null, bool $verbose = false) : array
    {
        $report = function (string $message) use ($onProgress) {
            Log::info(__METHOD__ . ' | ' . $message);

            if ($onProgress) {
                $onProgress($message);
            }
        };

        $report('Connecting to switch ' . $switch->name . ' (' . $switch->ip_addr . ') over ssh');

        $vlanNumbers = $vlan ? [$vlan] : self::getVlanNumbersFromSwitch($switch, $report);

        if (empty($vlanNumbers)) {
            $report($vlan
                ? 'VLAN ' . $vlan . ' was requested but the switch did not report having it'
                : 'Could not read any vlans directly from switch ' . $switch->name);

            return [];
        }

        $report('Scanning ' . count($vlanNumbers) . ' vlan(s) directly on the switch: ' . implode(', ', $vlanNumbers));

        $collisions = [];

        foreach ($vlanNumbers as $vlanNumber) {
            //  Unsaved on purpose - this is only a name/network-id holder so we can reuse the
            //  existing DellS6100::getArp() signature without needing a synced db row.
            $interface = new NetworkMembersInterfaces([
                'name'                      =>  'VLAN ' . $vlanNumber,
                'iaas_network_member_id'    =>  $switch->id,
                'iaas_network_id'           =>  self::resolveNetworkIdForVlan($switch, $vlanNumber),
            ]);

            $report('Reading arp table of interface ' . $interface->name);

            $arpRecords = null;

            switch ($switch->switch_type) {
                case 'dells6100':
                    $arpRecords = DellS6100::getArp($switch, $interface);
                    break;
                default:
                    $report('Switch type "' . $switch->switch_type . '" is not supported for arp collection');
            }

            if (!$arpRecords) {
                $report('Switch did not respond to the arp query on interface ' . $interface->name);

                StateHelper::setState($switch, 'switch_not_responded', 'We tried to get the arp ' .
                    'records for this switch but it didnt respond.', StateHelper::STATE_ERROR);

                continue;
            }

            $report('Got ' . count($arpRecords) . ' arp record(s) on interface ' . $interface->name);

            if ($verbose && !$interface->iaas_network_id) {
                $report('Could not resolve which network vlan ' . $vlanNumber . ' belongs to - ' .
                    'ip ownership checks will be best-effort (matched by ip only) for this vlan.');
            }

            $collisions = array_merge(
                $collisions,
                self::findCollisionsOnInterface($interface, $arpRecords, $report, $verbose)
            );
        }

        if (count($collisions)) {
            StateHelper::setState($switch, 'ip_collision_detected', count($collisions) .
                ' ip collision(s) found while scanning arp tables.', StateHelper::STATE_WARNING);
        }

        $report('Scan complete. ' . count($collisions) . ' collision(s) found in total.');

        return $collisions;
    }

    /**
     * Asks the switch itself which vlans exist ("show vlan brief") instead of relying on
     * NetworkMembersInterfaces being synced into the database, and returns their numbers.
     */
    private static function getVlanNumbersFromSwitch(NetworkMembers $switch, callable $report) : array
    {
        $vlanInterfaceNames = null;

        switch ($switch->switch_type) {
            case 'dells6100':
                $vlanInterfaceNames = DellS6100::getVlans($switch);
                break;
            default:
                $report('Switch type "' . $switch->switch_type . '" is not supported for vlan discovery');
        }

        if (!$vlanInterfaceNames) {
            return [];
        }

        $vlanNumbers = [];

        foreach ($vlanInterfaceNames as $name) {
            $number = trim(str_ireplace('vlan', '', $name));

            if ($number !== '' && is_numeric($number)) {
                $vlanNumbers[] = (int) $number;
            }
        }

        return array_values(array_unique($vlanNumbers));
    }

    /**
     * Best-effort lookup of which Networks row a vlan belongs to, used only to scope the
     * IpAddresses ownership check. Looked up directly against Networks (by vlan number + the
     * switch's network pool), the same way NetworkMembers\Initiate resolves it - so this works
     * even when NetworkMembersInterfaces has never been synced for this switch.
     */
    private static function resolveNetworkIdForVlan(NetworkMembers $switch, int $vlanNumber) : ?int
    {
        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('vlan', $vlanNumber)
            ->where('iaas_network_pool_id', $switch->iaas_network_pool_id)
            ->first();

        return $network->id ?? null;
    }

    /**
     * Goes through every ip seen in the arp table of one interface and, for each one, reports
     * (when $verbose) whether it matches what we have on file, is unregistered, or is a
     * confirmed collision. Flags:
     *  - any ip answered for by more than one mac (a real, on-the-wire collision)
     *  - any ip whose answering mac does not match the network card / custom mac we have on
     *    file for it in IpAddresses
     */
    private static function findCollisionsOnInterface(NetworkMembersInterfaces $interface, array $arpRecords, callable $report, bool $verbose) : array
    {
        $macsByIp = [];

        foreach ($arpRecords as $arp) {
            $macsByIp[$arp['ip']][] = strtolower($arp['mac']);
        }

        $collisions = [];

        foreach ($macsByIp as $ip => $macs) {
            $macs = array_values(array_unique($macs));

            if (count($macs) > 1) {
                $collisions[] = self::reportCollision($interface, $ip, $macs, 'multiple_macs_for_ip', null, $report);

                continue;
            }

            $mac = $macs[0];

            $ipAddress = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('ip_addr', $ip)
                ->when($interface->iaas_network_id, function ($query) use ($interface) {
                    $query->where('iaas_network_id', $interface->iaas_network_id);
                })
                ->first();

            if (!$ipAddress) {
                if ($verbose) {
                    $report('IP ' . $ip . ' (mac ' . $mac . ') is on the wire but not registered in ' .
                        'IpAddresses at all - possibly a manually assigned address we dont know about.');
                }

                continue;
            }

            $dbMac = null;

            if ($ipAddress->iaas_virtual_network_card_id) {
                $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->find($ipAddress->iaas_virtual_network_card_id);

                $dbMac = $vnc && $vnc->mac_addr ? strtolower($vnc->mac_addr) : null;
            } elseif ($ipAddress->custom_mac_addr) {
                $dbMac = strtolower($ipAddress->custom_mac_addr);
            }

            if (!$dbMac) {
                if ($verbose) {
                    $report('IP ' . $ip . ' (mac ' . $mac . ') is registered in IpAddresses but has no ' .
                        'mac on file to compare against.');
                }

                continue;
            }

            if ($dbMac != $mac) {
                $collisions[] = self::reportCollision(
                    $interface,
                    $ip,
                    [$mac, $dbMac],
                    'ip_owned_by_different_mac',
                    $ipAddress,
                    $report
                );

                continue;
            }

            if ($verbose) {
                $report('IP ' . $ip . ' (mac ' . $mac . ') OK - matches our records.');
            }
        }

        return $collisions;
    }

    private static function reportCollision(
        NetworkMembersInterfaces $interface,
        string $ip,
        array $macs,
        string $reason,
        ?IpAddresses $ipAddress,
        callable $report
    ) : array {
        $collision = [
            'ip'            =>  $ip,
            'macs'          =>  $macs,
            'reason'        =>  $reason,
            'interface'     =>  $interface->name,
            'network_id'    =>  $interface->iaas_network_id,
        ];

        $report('Collision: ip ' . $ip . ' answered by mac(s) ' . implode(', ', $macs) . ' (' . $reason . ')');

        Log::warning(__METHOD__ . ' | IP collision detected: ' . print_r($collision, true));

        if ($ipAddress) {
            StateHelper::setState($ipAddress, 'ip_collision_detected', 'IP ' . $ip .
                ' is answered on the wire by mac ' . $macs[0] . ' but our records say it belongs to a ' .
                'different network card. This most likely means the address was assigned manually.',
                StateHelper::STATE_WARNING);
        }

        return $collision;
    }
}
