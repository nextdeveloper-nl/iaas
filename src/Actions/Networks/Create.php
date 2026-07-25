<?php

namespace NextDeveloper\IAAS\Actions\Networks;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\GatewaysService;
use NextDeveloper\IAAS\Services\NetworksService;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
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

        //  Gateway auto-provisioning is opt-out, not mandatory - a caller (e.g.
        //  VdcServices::createWizard()) can pass ['create_gateway' => false] to skip it
        //  entirely for this network, regardless of whether the cloud node is
        //  firewall-enabled. Defaults to true to preserve the existing automatic behavior
        //  for every other caller that doesn't pass this param.
        $createGateway = is_array($this->params) ? ($this->params['create_gateway'] ?? true) : true;

        if (!$createGateway) {
            CommentsService::createSystemComment(
                'Gateway provisioning was skipped for this network because create_gateway was set to false.',
                $this->model
            );

            $this->setProgress(100, 'Network initiated');
            return;
        }

        $cloudNode = NetworksService::getCloudNode($this->model);

        if(!in_array($cloudNode->slug, config('leo.iaas.firewall_enabled_cloud_nodes'))) {
            CommentsService::createSystemComment(
                'A gateway was not provisioned automatically for this network: cloud node "' .
                $cloudNode->slug . '" is not firewall-enabled.',
                $this->model
            );

            $this->setProgress(100, 'Network initiated');
            return;
        }

        $this->setProgress(50, 'Initiating firewall');

        //  Provisioning failure (e.g. no matching firewall image) should not fail the
        //  whole network-creation action - the network itself was created successfully
        //  above. Caught here, logged, and left as a visible comment on the network so
        //  the customer/support can see exactly why there's no gateway, instead of the
        //  action either silently completing or dying with an uncaught exception that
        //  a queue worker would just retry from the top (redoing switch config, events,
        //  etc.) until it lands in failed_jobs with nothing user-visible to show for it.
        try {
            $gateway = GatewaysService::provisionForNetwork($this->model);

            if ($gateway) {
                $this->model->update([
                    'iaas_gateway_id' => $gateway->id,
                ]);
            }

            $this->setProgress(100, 'Network initiated');
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' | Gateway provisioning failed for network ' . $this->model->uuid . ': ' . $e->getMessage());

            CommentsService::createSystemComment(
                'We could not provision a gateway for this network: ' . $e->getMessage(),
                $this->model
            );

            $this->setProgress(100, 'Network initiated, but gateway provisioning failed: ' . $e->getMessage());
        }
    }
}
