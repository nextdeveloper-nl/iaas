<?php

namespace NextDeveloper\IAAS\Actions\NetworkMembersInterfaces;

use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use PharIo\Manifest\Author;

/**
 * This actions updates the list of ip addresses by looking at the arp values of the switch.
 */
class UpdateIpsWithArp extends AbstractAction
{
    public const EVENTS = [
        'updating-ips-with-arp:NextDeveloper\IAAS\NetworkMembers',
        'updated-ips-with-arp:NextDeveloper\IAAS\NetworkMembers',
    ];

    public function __construct(NetworkMembersInterfaces $interface)
    {
        $this->model = $interface;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Network member interface arp check initializing');

        Events::fire('updating-ips-with-arp:NextDeveloper\IAAS\NetworkMembers', $this->model);

        $this->setProgress(1, 'Getting arp information for related interfaces of the switch');

        $step = 1;

        $this->setProgress(1 + $step, 'Updating interface ' . $this->model->name);

        $arpRecords = null;

        $switch = NetworkMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->model->iaas_network_member_id)
            ->first();

        switch ($switch->switch_type) {
            case 'dells6100':
                $arpRecords = DellS6100::getArp($switch, $this->model);
                break;
        }

        if(!$arpRecords) {
            StateHelper::setState($this->model, 'switch_not_responded', 'We tried to get ' .
                'the arp records for this switch but it didnt responded.', 'error');

            return;
        }

        foreach ($arpRecords as $arp) {
            Log::info(__METHOD__ . ' | Found ARP record: ' . print_r($arp, true));

            $ipOwner    = $this->getOwnerOfMac($arp['mac']);

            $ipAddress  =   IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('ip_addr', $arp['ip'])
                ->first();

            $vnc = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('mac_addr', $arp['mac'])
                ->first();

            if($ipAddress) {
                if($ipOwner) {
                    if(!$ipAddress->iam_account_id) {
                        $ipAddress->updateQuietly([
                            'iam_account_id'    =>  $ipOwner->id,
                            'iam_user_id'       =>  $ipOwner->iam_user_id
                        ]);
                    }

                    //  This means that there is an owner, and there is an account but they are not matching.
                    //  This is actually a serious problem.
                    if($ipAddress->iam_account_id != $ipOwner->id) {
                        //  We need to send an email to DC owner and tell them that there is a problem here.
                        StateHelper::setState($this->model, 'unknown_ip_owner', 'The IP address '
                            . $ipAddress->ip . ' is not owned by ' . $ipOwner->name . ' but it is in the database.' .
                            ' Please check the ownership of this IP address. May cause problems.');
                    }
                }
            } else {
                //  We need to create the IP address
                IpAddresses::create([
                    'ip_addr'                =>  $arp['ip'],
                    'iaas_virtual_network_card_id'  =>  $vnc ? $vnc->id : null,
                    'iam_account_id'    =>  $ipOwner ? $ipOwner->id : null,
                    'iam_user_id'    =>  $ipOwner ? $ipOwner->iam_user_id : null,
                    'iaas_network_id'   =>  $this->model->iaas_network_id
                ]);
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
