<?php

namespace NextDeveloper\IAAS\Actions\NetworkMembers;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Connects to a switch over SSH and reads the arp table of every vlan interface, looking
 * for ip addresses that are being answered for on the wire by more than one mac address, or
 * by a mac address that does not match what our own records say should own that ip. Both
 * situations only happen when an ip has been assigned manually outside of our provisioning
 * system, so this is the signal we use to detect manual/duplicate ip collisions.
 */
class DetectIpCollisions extends AbstractAction
{
    private $vlan;

    public const EVENTS = [
        'detecting-ip-collisions:NextDeveloper\IAAS\NetworkMembers',
        'detected-ip-collisions:NextDeveloper\IAAS\NetworkMembers',
        'ip-collision-found:NextDeveloper\IAAS\NetworkMembers',
    ];

    public const PARAMS = [
        'vlan' =>  'nullable|integer',
    ];

    public function __construct(NetworkMembers $switch, $params = null, $previous = null)
    {
        if ($params && array_key_exists(0, $params)) {
            $params = $params[0];
        }

        $this->model = $switch;
        $this->vlan = $params['vlan'] ?? null;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'IP collision scan starting for switch ' . $this->model->name);

        Events::fire('detecting-ip-collisions:NextDeveloper\IAAS\NetworkMembers', $this->model);

        $interfaces = NetworkMembersInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_network_member_id', $this->model->id)
            ->where('name', 'ilike', $this->vlan ? 'vlan ' . $this->vlan : 'vlan %')
            ->get();

        if ($interfaces->isEmpty()) {
            $this->setProgress(100, $this->vlan
                ? 'VLAN ' . $this->vlan . ' not found on switch ' . $this->model->name
                : 'No vlan interfaces found on switch ' . $this->model->name);

            return [];
        }

        $step = ceil(98 / count($interfaces));
        $progress = 1;
        $collisions = [];

        foreach ($interfaces as $interface) {
            $progress += $step;
            $this->setProgress($progress, 'Reading arp table of interface ' . $interface->name);

            $arpRecords = null;

            switch ($this->model->switch_type) {
                case 'dells6100':
                    $arpRecords = DellS6100::getArp($this->model, $interface);
                    break;
            }

            if (!$arpRecords) {
                StateHelper::setState($this->model, 'switch_not_responded', 'We tried to get the arp ' .
                    'records for this switch but it didnt respond.', StateHelper::STATE_ERROR);

                continue;
            }

            $collisions = array_merge($collisions, $this->findCollisionsOnInterface($interface, $arpRecords));
        }

        if (count($collisions)) {
            StateHelper::setState($this->model, 'ip_collision_detected', count($collisions) .
                ' ip collision(s) found while scanning arp tables.', StateHelper::STATE_WARNING);
        }

        Events::fire('detected-ip-collisions:NextDeveloper\IAAS\NetworkMembers', $this->model, [
            'collisions'    =>  $collisions,
        ]);

        $this->setProgress(100, 'IP collision scan completed for switch ' . $this->model->name);

        return $collisions;
    }

    /**
     * Groups the arp table of one interface by ip and flags:
     *  - any ip answered for by more than one mac (a real, on-the-wire collision)
     *  - any ip whose answering mac does not match the network card we have on file for it
     */
    private function findCollisionsOnInterface(NetworkMembersInterfaces $interface, array $arpRecords) : array
    {
        $macsByIp = [];

        foreach ($arpRecords as $arp) {
            $macsByIp[$arp['ip']][] = strtolower($arp['mac']);
        }

        $collisions = [];

        foreach ($macsByIp as $ip => $macs) {
            $macs = array_values(array_unique($macs));

            if (count($macs) > 1) {
                $collisions[] = $this->reportCollision($interface, $ip, $macs, 'multiple_macs_for_ip');

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
                $collisions[] = $this->reportCollision(
                    $interface,
                    $ip,
                    [$mac, strtolower($vnc->mac_addr)],
                    'ip_owned_by_different_mac',
                    $ipAddress
                );
            }
        }

        return $collisions;
    }

    private function reportCollision(
        NetworkMembersInterfaces $interface,
        string $ip,
        array $macs,
        string $reason,
        ?IpAddresses $ipAddress = null
    ) : array {
        $collision = [
            'ip'            =>  $ip,
            'macs'          =>  $macs,
            'reason'        =>  $reason,
            'interface'     =>  $interface->name,
            'network_id'    =>  $interface->iaas_network_id,
        ];

        Log::warning(__METHOD__ . ' | IP collision detected: ' . print_r($collision, true));

        if ($ipAddress) {
            StateHelper::setState($ipAddress, 'ip_collision_detected', 'IP ' . $ip .
                ' is answered on the wire by mac ' . $macs[0] . ' but our records say it belongs to a ' .
                'different network card. This most likely means the address was assigned manually.',
                StateHelper::STATE_WARNING);
        }

        Events::fire('ip-collision-found:NextDeveloper\IAAS\NetworkMembers', $this->model, $collision);

        return $collision;
    }
}
