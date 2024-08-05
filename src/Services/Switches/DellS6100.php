<?php

namespace NextDeveloper\IAAS\Services\Switches;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
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
        //$interfaces = self::getPhysicalInterfaces($nm);
        $interfaces = array_merge($interfaces, self::getVlans($nm));
        //$interfaces = array_merge($interfaces, self::getVLTs($nm));

        return $interfaces;
    }

    public static function getPhysicalInterfaces(NetworkMembers $nm) {
        $command = 'show interfaces status';
        $result = self::execute($nm, $command);

        $output = $result[0]['output'];

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

        $output = $result[0]['output'];

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

        $lines = $config[0]['output'];
        $lines = explode(PHP_EOL, $lines);
        $lines = self::getLinesAfter($lines, 'interface ' . $interface->name);

        return implode(PHP_EOL, $lines);
    }
}
