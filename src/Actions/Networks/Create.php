<?php

namespace NextDeveloper\IAAS\Actions\Networks;

use App\Services\IAAS\VirtualMachineServices;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Helpers\MetaHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAAS\Services\GatewaysService;
use NextDeveloper\IAAS\Services\IpAddressesService;
use NextDeveloper\IAAS\Services\NetworksService;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action converts the virtual machine into a template
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\Networks',
        'created:NextDeveloper\IAAS\Networks',
        'create-failed:NextDeveloper\IAAS\Networks'
    ];

    public function __construct(Networks $network, $params = null, $previousAction = null)
    {
        $this->model = $network;

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiating network');

        $networkMembers = NetworkMembers::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_network_pool_id', $this->model->iaas_network_pool_id)
            ->get();

        Events::fire('creating:NextDeveloper\IAAS\Networks', $this->model);

        foreach ($networkMembers as $member) {
            Log::info(__METHOD__ . ' | Configuring switch: ' . $member->name);

            switch ($member->switch_type) {
                case 'dells6100':
                    DellS6100::addNetworkToSwitch($this->model, $member);
                    break;
                case 'ovs':
                default:
                    //  Do nothing
            }
        }

        Events::fire('created:NextDeveloper\IAAS\Networks', $this->model);

        $this->setProgress(50, 'Initiating firewall');

        $cloudNode = NetworksService::getCloudNode($this->model);
        $repositories = CloudNodesService::getRepositories($cloudNode);

        $repositoryImage = RepositoryImages::where([
            'os'        =>  config('leo.iaas.firewall_os'),
            'distro'    =>  config('leo.iaas.firewall_distro'),
            'version'   =>  config('leo.iaas.firewall_version'),
        ])
            ->whereIn('iaas_repository_id', $repositories->pluck('id'))
            ->first();

        $defaultComputePool = ComputePoolsService::getDefaultPool($cloudNode);

        $publicNetwork = NetworksService::getPublicNetwork($cloudNode);

        $firewall = VirtualMachineServices::createWizard([
            'iaas_repository_image_id' =>  $repositoryImage->uuid,
            'iaas_compute_pool_id'  =>  $defaultComputePool->uuid,
            'iaas_network_id' => $publicNetwork->uuid,
            'name' => $this->model->name . ' VDC Firewall',
            'ram' => '2gb',
            'cpu' => 2,
            'disk' => 20,
            'backup_interval'     =>  'none',
            'backup_time'       =>  'in:12+4',
            'monitoring_enabled'    =>  false,
            'auto_deploy'       =>  false,
            'boot_after_deploy' =>  true,
        ]);

        $vif = VirtualNetworkCardsService::create([
            'name'  =>  'eth1',
            'iaas_virtual_machine_id'   =>  $firewall->id,
            'iaas_network_id'   =>  $this->model->id
        ]);

        if($vif) {
            MetaHelper::set($vif, 'auto_add_ip_v4', false);

            $ip = IpAddressesService::create([
                'iaas_network_id'   =>  $this->model->id,
                'iaas_virtual_network_card_id'  =>  $vif->id,
                'ip_addr'   =>  '10.128.0.1/32',
            ]);
        }

        $gateway = GatewaysService::create([
            'name'  =>  $this->model->name . ' Gateway',
            'iaas_virtual_machine_id'   =>  $firewall->id,
            'gateway_data'  =>  [],
            'is_public'  =>  false
        ]);

        $this->model->update([
            'iaas_gateway_id' => $gateway->id,
        ]);

        /**
         * 1) Here we need to setup the disk
         * 2) We need to setup the network
         */
        dispatch(new Commit($firewall));

        $this->setProgress(100, 'Network initiated');
    }
}
