<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
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
     * Connects to the given switch over SSH and reads the arp table of every vlan interface
     * (or a single one, if $vlan is given), looking for ip addresses that are being answered
     * for on the wire by more than one mac address, or by a mac address that does not match
     * what our own records say should own that ip. Both situations only happen when an ip has
     * been assigned manually outside of our provisioning system.
     *
     * $onProgress, if given, is called with a human readable string after every meaningful
     * step, so callers can surface what is happening live (e.g. a console command) instead of
     * only finding out about the result after the whole scan is done.
     *
     * @return array List of collisions found, each with ip / macs / reason / interface / network_id
     */
    public static function detectIpCollisions(NetworkMembers $switch, ?int $vlan = null, ?callable $onProgress = null) : array
    {
        $report = function (string $message) use ($onProgress) {
            Log::info(__METHOD__ . ' | ' . $message);

            if ($onProgress) {
                $onProgress($message);
            }
        };

        $report('Connecting to switch ' . $switch->name . ' (' . $switch->ip_addr . ') over ssh');

        $interfaces = NetworkMembersInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_network_member_id', $switch->id)
            ->where('name', 'ilike', $vlan ? 'vlan ' . $vlan : 'vlan %')
            ->get();

        if ($interfaces->isEmpty()) {
            $report($vlan
                ? 'VLAN ' . $vlan . ' not found on switch ' . $switch->name
                : 'No vlan interfaces found on switch ' . $switch->name);

            return [];
        }

        $report('Found ' . $interfaces->count() . ' vlan interface(s) to scan');

        $collisions = [];

        foreach ($interfaces as $interface) {
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

            $collisions = array_merge(
                $collisions,
                self::findCollisionsOnInterface($interface, $arpRecords, $report)
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
     * Groups the arp table of one interface by ip and flags:
     *  - any ip answered for by more than one mac (a real, on-the-wire collision)
     *  - any ip whose answering mac does not match the network card we have on file for it
     */
    private static function findCollisionsOnInterface(NetworkMembersInterfaces $interface, array $arpRecords, callable $report) : array
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
                ->where('iaas_network_id', $interface->iaas_network_id)
                ->first();

            if (!$ipAddress || !$ipAddress->iaas_virtual_network_card_id) {
                continue;
            }

            $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->find($ipAddress->iaas_virtual_network_card_id);

            if ($vnc && $vnc->mac_addr && strtolower($vnc->mac_addr) != $mac) {
                $collisions[] = self::reportCollision(
                    $interface,
                    $ip,
                    [$mac, strtolower($vnc->mac_addr)],
                    'ip_owned_by_different_mac',
                    $ipAddress,
                    $report
                );
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
