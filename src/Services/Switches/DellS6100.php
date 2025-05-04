<?php

namespace NextDeveloper\IAAS\Services\Switches;

use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Exceptions\CannotContinueException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class DellS6100 extends AbstractSwitches
{
    /**
     * Get the list of interfaces
     * @param NetworkMembers $nm
     * @return mixed
     */
    public static function getInterfaces(NetworkMembers $nm)
    {
        $interfaces = [];
        $interfaces = self::getPhysicalInterfaces($nm);
        //$interfaces = array_merge($interfaces, self::getVlans($nm));
        //$interfaces = array_merge($interfaces, self::getVLTs($nm));

        return $interfaces;
    }

    /**
     * Applying the network to the related network interfaces. If the network interface is given null, then
     * function will apply all interfaces available.
     *
     * @param Networks $network
     * @param NetworkMembersInterfaces|null $physicalInterfaces
     * @return void
     */
    public static function addNetworkToSwitch(Networks $network, NetworkMembers $member) {
        $commands = [
            'configure',
            'interface vlan ' . $network->vlan
        ];

        $physicalInterfaces = NetworkMembersInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_network_member_id', $member->id)
            ->where('name', 'not like', 'vlan%')
            ->where('is_up', true)
            ->get();

        $i = 0;

        foreach ($physicalInterfaces as $interface) {
            $c = 'tagged ' . $interface->name;
            $c = str_replace('tenGigabitEthernet', 'Tengigabitethernet', $c);

            $commands[] = $c;

            $i++;
        }

        $commands[] = 'no shutdown';

        $response = $member->performSSHCommand($commands);

        Log::debug(__METHOD__ . ' | Response: ' . $response['output']);

        return true;
    }

    public static function isVlanExists(NetworkMembers $nm, $vlan)
    {
        $command = 'show running-config interface vlan ' . $vlan;
        $result = self::execute($nm, $command);

        $output = $result['output'];

        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            if(Str::contains($line, 'interface Vlan ' . $vlan)) {
                return true;
            }
        }
    }

    public static function getPhysicalInterfaces(NetworkMembers $nm) {
        $command = 'show interfaces status';
        $result = self::execute($nm, $command);

        $output = $result['output'];

        $lines = explode("\n", $output);

        $interfaces = [];

        foreach ($lines as $line) {
            if(Str::startsWith($line, 'Fo ') || Str::startsWith($line, 'Te ') || Str::startsWith($line, 'Gi ')) {
                $interfaces[] = $line;
            }
        }

        foreach ($interfaces as &$interface) {
            $exploded = explode(' ', $interface);

            switch ($exploded[0]) {
                case 'Fo':
                    $interface = 'fortyGigE ' . $exploded[1];
                    break;
                case 'Te':
                    $interface = 'tenGigabitEthernet ' . $exploded[1];
                    break;
                case 'Gi':
                    $interface = 'gigabitEthernet ' . $exploded[1];
                    break;
            }
        }

        return $interfaces;
    }

    public static function getVlans(NetworkMembers $nm)
    {
        $command = 'show vlan brief';
        $result = self::execute($nm, $command);

        $output = $result['output'];

        $lines = explode("\n", $output);

        $vlanLines = [];

        $passedLines = false;

        foreach ($lines as &$line) {
            if(Str::contains($line, '----')) {
                $passedLines = true;
                continue;
            }

            if(!$passedLines)
                continue;

            if(Str::contains($line, '#')) {
                continue;
            }

            $vlanLines[] = $line;
        }

        $interfaces = [];

        foreach ($vlanLines as $line) {
            $exploded = explode(' ', $line);

            $interfaces[] = 'vlan ' . $exploded[0];
        }

        return $interfaces;
    }

    public static function getVLTs(NetworkMembers $nm)
    {
        return [];
    }

    public static function getInterfaceConfiguration(NetworkMembersInterfaces $interface)
    {
        $switch = NetworkMembers::withoutGlobalScope(AuthorizationScope::class)
            ->find($interface->iaas_network_member_id);

        $config = $switch->performSSHCommand('show running-config interface ' . $interface->name);

        if(!$config) {
            StateHelper::setState($interface, 'configuration', 'Failed to get configuration', StateHelper::STATE_ERROR);
            StateHelper::setState($switch, 'configuration', 'Failed to get configuration', StateHelper::STATE_ERROR);

            return '[NEED-REFRESH-CANNOT-GET-CONFIG]';
        }

        $lines = $config['output'];
        $lines = explode(PHP_EOL, $lines);
        $lines = self::getLinesAfter($lines, 'interface ' . $interface->name);

        return implode(PHP_EOL, $lines);
    }

    public static function getArp(NetworkMembers $nm, NetworkMembersInterfaces $interface)
    {
        $command = 'show arp interface ' . $interface->name;
        $result = self::execute($nm, $command);

        if(!$result)
            return null;

        $output = $result['output'];

        $lines = explode("\n", $output);

        $arp = [];

        foreach ($lines as $line) {
            if(Str::contains($line, 'Internet')) {
                $exploded = explode(' ', $line);
                foreach ($exploded as $key => $value) {
                    if($value == '') {
                        unset($exploded[$key]);
                    }
                }

                //  Here we reorder the array
                $exploded = array_values($exploded);

                $arp[] = [
                    'ip'    =>  $exploded[1],
                    'mac'   =>  $exploded[3]
                ];
            }
        }

        return $arp;
    }
}
