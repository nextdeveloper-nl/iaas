<?php
namespace NextDeveloper\IAAS\Actions\DhcpServers;

use Illuminate\Support\Facades\Http;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\DhcpServers;

/**
 * This class initiates a datacenter, meaning that it will create the necessary resources for a datacenter. It includes
 * but is not limited to:
 * - Cloud Node
 * - ComputePool
 * - StoragePool
 * - NetworkPool
 */
class UpdateConfiguration extends AbstractAction
{
    /**
     * We are using these events here to be able to create chain reactions. And to be able to inform the
     */
    public const EVENTS = [
        'updating-configuration:NextDeveloper\IAAS\DhcpServers',
        'updated-configuration:NextDeveloper\IAAS\DhcpServers',
        'update-failed:NextDeveloper\IAAS\DhcpServers',
    ];

    public function __construct(DhcpServers $dhcpServers)
    {
        $this->model = $dhcpServers;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Starting to update the configuration');
        Events::fire('updating-configuration:NextDeveloper\IAAS\DhcpServers', $this->model);

        //  This means we need to use the API URL to update the configuration
        if($this->model->api_url) {
            //  In this verstion we are just triggering the DHCP server to update the configuration
            //  And hope for the best :D
            Http::get($this->model->api_url);
        }

        $this->setFinished('Update finished');
        Events::fire('updated-configuration:NextDeveloper\IAAS\DhcpServers', $this->model);
    }
}
