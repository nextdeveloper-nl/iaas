<?php

namespace NextDeveloper\IAAS\Services\Switches;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;

abstract class AbstractSwitches
{
    /**
     * Get the list of interfaces
     * @param NetworkMembers $nm
     * @return mixed
     */
    abstract public static function getInterfaces(NetworkMembers $nm);

    abstract public static function getInterfaceConfiguration(NetworkMembersInterfaces $interface);

    public static function getLinesAfter($lines, $start) {
        $result = [];
        $found = false;
        foreach ($lines as $line) {
            if ($found) {
                $result[] = trim($line);
            }
            if (Str::startsWith(strtolower($line), strtolower($start))) {
                $found = true;
            }
        }

        unset($result[count($result) - 1]);

        return $result;
    }

    public static function execute(NetworkMembers $nm, $command) {
        return $nm->performSSHCommand($command);
    }
}
