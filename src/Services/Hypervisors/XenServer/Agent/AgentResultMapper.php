<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent;

/**
 * Turns agent JSON into the array shape the rest of IAAS already expects from the SSH path.
 *
 * AbstractXenService::parseResult() returns one flat, hyphen-keyed array of strings per
 * object (uuid, name-label, power-state, VCPUs-max, memory-static-max, ...). The sync code and
 * VirtualMachines.hypervisor_data consumers read exactly those keys, so the agent driver must
 * hand them the same shape. The agent's *.param_list operations return
 * {params:{...}, maps:{...}, sets:{...}} instead.
 */
class AgentResultMapper
{
    /**
     * Flattens an agent param_list reply (params + maps + sets) into the xe-style array.
     * Scalars become strings the way xe prints them (true => 'true'); maps and sets stay arrays.
     * A reply that is already flat is returned as-is (stringified), so a shape change on the
     * agent side degrades to "still usable" instead of an empty array.
     */
    public static function paramList(array $reply): array
    {
        if (!isset($reply['params']) && !isset($reply['maps']) && !isset($reply['sets'])) {
            return array_map([self::class, 'scalar'], $reply);
        }

        $flat = array_map([self::class, 'scalar'], $reply['params'] ?? []);

        foreach (($reply['maps'] ?? []) as $key => $value) {
            $flat[$key] = (array) $value;
        }

        foreach (($reply['sets'] ?? []) as $key => $value) {
            $flat[$key] = (array) $value;
        }

        return $flat;
    }

    private static function scalar(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return is_scalar($value) ? (string) $value : $value;
    }
}
