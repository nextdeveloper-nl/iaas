<?php

namespace NextDeveloper\IAAS\Actions\VirtualNetworkCards;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\DhcpServersService;
use NextDeveloper\IAAS\Services\IpAddressesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;

/**
 * This action converts the virtual machine into a template
 */
class AddRandomIpV4 extends AbstractAction
{
    public const EVENTS = [
        'adding-random-ip:NextDeveloper\IAAS\VirtualNetworkCards',
        'added-random-ip:NextDeveloper\IAAS\VirtualNetworkCards',
        'failed-adding-random-ip:NextDeveloper\IAAS\VirtualNetworkCards'
    ];

    public function __construct(VirtualNetworkCards $vnc)
    {
        $this->model = $vnc;

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine started');

        $this->setProgress(10, 'Getting the network required for setting IP');
        $network = VirtualNetworkCardsService::getConnectedNetwork($this->model);

        $this->setProgress(20, 'Finding the next available IP');
        $nextIp = IpAddressesService::getNextIpAvailable($network);

        $this->setProgress(40, 'Assigning and reserving the IP address for this network card.');
        $ipAddress = VirtualNetworkCardsService::assignIpToCard($nextIp, $this->model);

        $this->setProgress(60, 'Checking if we have a managed DHCP service for the network.');
        $dhcp = DhcpServersService::getById($network->iaas_dhcp_server_id);

        if (!$dhcp) {
            $this->setFinished('Seems like we dont have managed DHCP service, that is why I am ' .
                'finishing the job here.');

            Log::error('While adding IPv4 we cannot find a DHCP that is why we are skipping' .
                ' this step and finishing the add random IP v4 process here. If there was a dhcp I' .
                ' would continue triggering the DHCP to renew the IP configuration.');
        }

        //  Here we need to trigger the DHCP
        $this->setProgress(80, 'Triggering the dhcp service to renew the configuration.');

        $this->model->status = 'initiated';
        $this->model->save();

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
