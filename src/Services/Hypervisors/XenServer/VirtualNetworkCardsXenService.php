<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\NetworksService;
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

        if(!$vif->hypervisor_data) {
            self::sync($vif);
        }

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

    public static function sync(VirtualNetworkCards $vif)
    {
        $vm = self::getVirtualMachine($vif);

        $existingVifs = VirtualMachinesXenService::getVifs($vm);

        if(!$existingVifs[0]) {
            Log::debug(__METHOD__ . '| No VIFS found for this VM. ' .
                'We should create a new VIF for this VM. ' .
                'VIF UUID: ' . $vif->uuid);
            //  This means that we actually have a network card in the database but we dont have it in the virtual machine
            if($vif->is_draft) {
                Log::debug(__METHOD__ . '| VIF is in draft state, therefore we are creating it.');
                //  This means that the VIF is not yet created in the hypervisor, so we actually should create it.
                if($vm->status == 'halted') {
                    Log::debug(__METHOD__ . '| VM is halted, trying to create the VIF.');

                    $network = NetworksService::getById($vif->iaas_network_id);
                    $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                        ->where('iaas_compute_member_id', $vm->iaas_compute_member_id)
                        ->where('vlan', $network->vlan)
                        ->first();

                    //  VM is halted so we can move on. But if the VM is not halted, we should not create the VIF.
                    VirtualMachinesXenService::createVif(
                        $vm,
                        $network->uuid,
                        $vif->device_number
                    );

                    $existingVifs = VirtualMachinesXenService::getVifs($vm);
                }
            }
        }

        foreach($existingVifs as $xenVif) {
            if($vif->device_number == $xenVif['device']) {
                $params = VirtualMachinesXenService::getVifParams($vm, $xenVif['uuid']);

                $vifParams = $params[0];

                $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                    ->where('network_uuid', $vifParams['network-uuid'])
                    ->first();

                $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                    ->where('vlan', $cmni->vlan)
                    ->first();

                $vif->update([
                    'hypervisor_uuid'   => $vifParams['uuid'],
                    'hypervisor_data'   => $vifParams,
                    'mac_addr'          => $vifParams['MAC'],
                    'iaas_network_id'   =>  $network ? $network->id : null,
                    'bandwitdh_limit'   =>  -1,
                    'is_draft'          =>  false
                ]);
            }
        }
    }
}
