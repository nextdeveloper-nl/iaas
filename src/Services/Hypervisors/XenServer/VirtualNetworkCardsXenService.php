<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class VirtualNetworkCardsXenService extends AbstractXenService
{
    public const LOCKED = 'locked';
    public const UNLOCKED = 'unlocked';
    public const DEFAULT = 'network_default';
    public const DISABLED = 'disabled';

    private const LOG_HEADER = '[VifXenService]';

    public static function setIpv4Allowed(VirtualNetworkCards $vif)
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::debug(__METHOD__ . ' Setting allowed IP addresses for VIF: ' . $vif->uuid);

        $ips = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_network_card_id', $vif->id)
            ->pluck('ip_addr')
            ->toArray();

        $implodedIps = implode(',', $ips);
        $implodedIps = str_replace('/32', '', $implodedIps);

        $command = 'xe vif-param-set uuid=' . $vif->hypervisor_data['uuid'] . ' ipv4-allowed=' . $implodedIps;

        if(config('leo.debug.iaas.compute_members'))
            Log::debug(self::LOG_HEADER . ' set ip v4 allowed command is: ' . $command);

        $computeMember = self::getComputeMember($vif);

        $result = self::performCommand($command, $computeMember);

        $parsedResult = self::parseResult($result['output']);

        if(config('leo.debug.iaas.compute_members'))
            Log::debug(self::LOG_HEADER . ' Set allowed Ipv4 result is: ' . print_r($parsedResult, true));

        return $result;
    }

    public static function setLockingState(VirtualNetworkCards $vif, $state)
    {
        if(config('leo.debug.iaas.compute_members'))
            Log::debug(__METHOD__ . ' Locking the network card state to: ' . $state);

        $command = '';

        switch ($state) {
            case self::LOCKED:
                $command = 'xe vif-param-set uuid=' . $vif->hypervisor_data['uuid'] . ' locking-mode=locked';
                break;
            case self::UNLOCKED:
                $command = 'xe vif-param-set uuid=' . $vif->hypervisor_data['uuid'] . ' locking-mode=unlocked';
                break;
            case self::DISABLED:
                $command = 'xe vif-param-set uuid=' . $vif->hypervisor_data['uuid'] . ' locking-mode=disabled';
                break;
            case self::DEFAULT:
                $command = 'xe vif-param-set uuid=' . $vif->hypervisor_data['uuid'] . ' locking-mode=network_default';
                break;
        }

        if(config('leo.debug.iaas.compute_members'))
            Log::debug(self::LOG_HEADER . ' Locking state command is: ' . $command);

        $computeMember = self::getComputeMember($vif);
        $result = self::performCommand($command, $computeMember);

        $parsedResult = self::parseResult($result['output']);

        if(config('leo.debug.iaas.compute_members'))
            Log::debug(self::LOG_HEADER . ' Locking state result is: ' . print_r($parsedResult, true));

        return $parsedResult;
    }

    public static function getVirtualMachine(VirtualNetworkCards $vif) : ?VirtualMachines
    {
        return VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vif->iaas_virtual_machine_id)
            ->first();
    }

    public static function getComputeMember(VirtualNetworkCards $vif) : ?ComputeMembers
    {
        $vm = self::getVirtualMachine($vif);

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();
    }

    public static function performCommand($command, ComputeMembers $computeMember) : ?array
    {
        if($computeMember->is_management_agent_available == true) {
            return $computeMember->performAgentCommand($command);
        } else {
            return $computeMember->performSSHCommand($command);
        }
    }
}
