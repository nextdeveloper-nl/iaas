<?php

namespace NextDeveloper\IAAS\Actions\NetworkMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This actions updates the list of ip addresses by looking at the arp values of the switch.
 */
class UpdateIpsWithArp extends AbstractAction
{
    public const EVENTS = [
        'updating-ips-with-arp:NextDeveloper\IAAS\NetworkMembers',
        'updated-ips-with-arp:NextDeveloper\IAAS\NetworkMembers',
    ];

    public function __construct(NetworkMembers $networkMember)
    {
        $this->model = $networkMember;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Network member initiating');

        Events::fire('updating-ips-with-arp:NextDeveloper\IAAS\NetworkMembers', $this->model);

        $this->setProgress(1, 'Getting arp information for related interfaces of the switch');

        $interfaces = NetworkMembersInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_network_member_id', $this->model->id)
            ->where('name', 'ilike', 'vlan %')
            ->get();

        $step = ceil(98 / count($interfaces));

        foreach ($interfaces as $interface) {
            $this->setProgress(1 + $step, 'Updating interface ' . $interface->name);

            switch ($this->model->switch_type) {
                case 'dells6100':
                    $arpRecords = DellS6100::getArp($this->model, $interface);
                    break;
            }

            if(!$arpRecords) {
                StateHelper::setState($this->model, 'switch_not_responded', 'We tried to get the arp records for this switch but it didnt responded.', 'error');
                continue;
            }

            foreach ($arpRecords as $arp) {
                $ipOwner    = $this->getOwnerOfMac($arp['mac']);

                $ipAddress  =   IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                    ->where('ip_addr', $arp['ip'])
                    ->first();

                $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                    ->where('mac_addr', $arp['mac'])
                    ->first();

                if($ipAddress) {
                    if($ipAddress->iam_account_id == $ipOwner->id) {
                        //  Everything is fine, we dont need to do anything
                    } else {
                        //  We need to send an email to DC owner and tell them that there is a problem here.
                        StateHelper::setState($this->model, 'unknown_ip_address', 'The IP address '
                            . $ipAddress->ip . ' is not owned by ' . $ipOwner->name . ' but it is in the database.' .
                            ' Please check the ownership of this IP address. May cause problems.');
                    }
                } else {
                    //  We need to create the IP address
                    IpAddresses::create([
                        'ip_addr'                =>  $arp['ip'],
                        'iaas_virtual_network_card_id'  =>  $vnc ? $vnc->id : null,
                        'custom_mac_addr'   =>  !$vnc ? $arp['mac'] : null,
                        'iam_account_id'    =>  $ipOwner ? $ipOwner->id : null,
                        'iam_user_id'    =>  $ipOwner ? $ipOwner->iam_user_id : null,
                        'iaas_network_id'   =>  $interface->iaas_network_id
                    ]);
                }
            }
        }

        Events::fire('updated-ips-with-arp:NextDeveloper\IAAS\NetworkMembers', $this->model);

        $this->setProgress(100, 'Network member initiated');
    }

    private function getOwnerOfMac($mac) {
        $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('mac_addr', $mac)
            ->first();

        if($vnc) {
            return Accounts::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $vnc->iam_account_id)
                ->first();
        } else {
            return null;
        }
    }

}
