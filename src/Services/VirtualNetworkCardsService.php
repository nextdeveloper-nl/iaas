<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\IAAS\Actions\DhcpServers\UpdateConfiguration;
use NextDeveloper\IAAS\Actions\VirtualNetworkCards\Attach;
use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualNetworkCardsService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualNetworkCardsXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for VirtualNetworkCards
 *
 * Class VirtualNetworkCardsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualNetworkCardsService extends AbstractVirtualNetworkCardsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create($data)
    {
        $vm = null;

        if(!array_key_exists('iaas_virtual_machine_id', $data)) {
            Log::error(__METHOD__ . ' | I have a network card creation request without a VM id. ' .
                'I cannot allow that. Sorry.');

            return null;
        }

        if(!array_key_exists('iaas_virtual_machine_id', $data)) {
            return null;
        }

        if(Str::isUuid($data['iaas_virtual_machine_id']))
            $vm = VirtualMachines::where('uuid', $data['iaas_virtual_machine_id'])->first();
        else
            $vm = VirtualMachines::where('id', $data['iaas_virtual_machine_id'])->first();

        if(!$vm) {
            //  If we still cannot find the virtual machine, this means that either this machine is deleted in the database
            //  or the virtual machine is not owned by the executer
            if(Str::isUuid($data['iaas_virtual_machine_id'])) {
                $vm = VirtualMachines::withoutGlobalScopes()
                    ->where('uuid', $data['iaas_virtual_machine_id'])
                    ->first();
            }
            else {
                $vm = VirtualMachines::withoutGlobalScopes()
                    ->where('id', $data['iaas_virtual_machine_id'])
                    ->first();
            }
        }

        if(!$vm) {
            Log::error(__METHOD__ . ' | So I have a data to create virtual network card with ' .
                'data below. But I cannot find the VM. This is kind of weird, ' .
                'that is why I am putting the data here too;' . print_r($data, true));

            Log::error(__METHOD__ . ' | Highly likely the VM is in the database but it is set as ' .
                'deleted. We may need to revive the VM.');

            return null;
        }

        $vifs = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

        //  We need to create a unique device number for the new VIF
        $data['device_number']  =  count($vifs);

        $vif = parent::create($data);

        return $vif;
    }

    public static function getIpAddresses(VirtualNetworkCards $card) : Collection
    {
        return IpAddresses::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_network_card_id', $card->id)
            ->get();
    }

    public static function getConnectedNetwork(VirtualNetworkCards $card) : Networks
    {
        return Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $card->iaas_network_id)
            ->first();
    }

    public static function assignIpToCard(string $ip, VirtualNetworkCards $card)
    {
        $ipAddress = IpAddressesService::create([
            'iaas_virtual_network_card_id'  =>  $card->id,
            'iaas_network_id'   =>  $card->iaas_network_id,
            'ip_addr'   =>  $ip
        ]);

        //  Here we check if the network had DHCP server, if it has we need to trigger the DHCP configuration also
        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $card->iaas_network_id)
            ->first();

        if($network->iaas_dhcp_server_id) {
            $dhcpServer = DhcpServers::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $network->iaas_dhcp_server_id)
                ->first();

            if($dhcpServer) {
                dispatch(new UpdateConfiguration($dhcpServer));
            } else {
                Log::warning(__METHOD__ . ' | I wanted to update the dhcp configuration, and according ' .
                    'to the records I should have a DHCP server but now I dont see it. Maybe we need to update ' .
                    'the DHCP server information.');

                StateHelper::setState(
                    $network,
                    'dhcp-server',
                    'Cannot find the dhcp server. I cannot update the dhcp configuration.',
                    StateHelper::STATE_WARNING
                );
            }
        }

        VirtualNetworkCardsXenService::setIpv4Allowed($card);
        VirtualNetworkCardsXenService::setLockingState($card, VirtualNetworkCardsXenService::LOCKED);

        return $ipAddress;
    }
}
