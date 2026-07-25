<?php

namespace NextDeveloper\IAAS\Services;

use App\Services\IAAS\VirtualMachineServices;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Helpers\MetaHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Contracts\FirewallRuleCapableInterface;
use NextDeveloper\IAAS\Contracts\NatCapableInterface;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Exceptions\CannotFindAvailableResourceException;
use NextDeveloper\IAAS\Jobs\Gateways\CollectGatewayCredentials;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractGatewaysService;
use NextDeveloper\IAAS\Services\Hypervisors\GatewayDriverManager;
use NextDeveloper\IAAS\ValueObjects\GatewayHealthStatus;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for Gateways
 *
 * Class GatewaysService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class GatewaysService extends AbstractGatewaysService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * Overrides AbstractGatewaysService::create() to strip credential fields
     * (ssh_username/ssh_password/api_token/api_url) from non-privileged requests -
     * these are meant to be populated by CollectGatewayCredentials/driver bootstrap()
     * during provisioning, not set directly by a client. Direct POST /iaas/gateways is
     * kept available (not removed, to avoid breaking existing route consumers) only for
     * a datacenter-admin/cloud-node-admin manually attaching an already-existing VM as a
     * gateway; everyone else should provision through GatewaysService::provisionForNetwork()
     * (via Actions\Networks\Create or the explicit Actions\Gateways\Create action) instead.
     */
    public static function create(array $data)
    {
        if (!(UserHelper::hasRole('datacenter-admin') || UserHelper::hasRole('cloud-node-admin'))) {
            unset($data['ssh_username'], $data['ssh_password'], $data['api_token'], $data['api_url']);
        }

        return parent::create($data);
    }

    /**
     * Provisions a firewall VM for $network and creates its Gateways row - the shared
     * implementation behind both the implicit "network created on a firewall-enabled
     * cloud node" flow (Actions\Networks\Create) and the explicit, user-triggered
     * Actions\Gateways\Create action.
     *
     * Returns null (same as the previous inline behavior) when the network's cloud
     * node isn't firewall-enabled and no gateway_type override was explicitly
     * requested - this is expected, "no gateway wanted" behavior, not an error, so it's
     * only recorded as an informational comment on $network, not thrown.
     *
     * Throws CannotFindAvailableResourceException (same exception type
     * ComputePoolsService::getDefaultPool()/NetworksService::getPublicNetwork() already
     * use for identical "nothing matched" situations below) when the gateway_type has
     * no configured image mapping, or no RepositoryImages row matches it - both are
     * real misconfigurations, not expected states, and previously caused an uncaught
     * TypeError (null->uuid) that silently killed the queued provisioning job instead
     * of surfacing anything to the caller. Callers are expected to catch this and
     * report it clearly (see Actions\Networks\Create and Actions\Gateways\Create).
     *
     * @param  Networks  $network
     * @param  array  $overrides  may contain 'gateway_type' to pick a driver other than
     *                            config('leo.iaas.default_firewall_type').
     * @throws CannotFindAvailableResourceException
     */
    public static function provisionForNetwork(Networks $network, array $overrides = []): ?Gateways
    {
        $cloudNode = NetworksService::getCloudNode($network);

        $gatewayType = $overrides['gateway_type'] ?? config('leo.iaas.default_firewall_type');

        if (!in_array($cloudNode->slug, config('leo.iaas.firewall_enabled_cloud_nodes'))
            && empty($overrides['gateway_type'])) {
            CommentsService::createSystemComment(
                'A gateway was not provisioned automatically for this network: cloud node "' .
                $cloudNode->slug . '" is not firewall-enabled.',
                $network
            );

            return null;
        }

        $imageConfig = config('leo.iaas.firewalls.' . $gatewayType);

        if (!$imageConfig) {
            throw new CannotFindAvailableResourceException(
                "We don't have an image configuration for gateway type \"{$gatewayType}\". " .
                'Please consult to your cloud provider.'
            );
        }

        $repositories = CloudNodesService::getRepositories($cloudNode);

        $repositoryImage = RepositoryImages::where([
            'os'        =>  $imageConfig['os'],
            'distro'    =>  $imageConfig['distro'],
            'version'   =>  $imageConfig['version'],
        ])
            ->whereIn('iaas_repository_id', $repositories->pluck('id'))
            ->first();

        if (!$repositoryImage) {
            throw new CannotFindAvailableResourceException(
                "We cannot find a firewall image matching \"{$imageConfig['distro']} {$imageConfig['version']}\" " .
                "for gateway type \"{$gatewayType}\" on cloud node \"{$cloudNode->name}\". " .
                'Please consult to your cloud provider.'
            );
        }

        $defaultComputePool = ComputePoolsService::getDefaultPool($cloudNode);

        $publicNetwork = NetworksService::getPublicNetwork($cloudNode);

        $firewall = VirtualMachineServices::createWizard([
            'iaas_repository_image_id' =>  $repositoryImage->uuid,
            'iaas_compute_pool_id'  =>  $defaultComputePool->uuid,
            'iaas_network_id' => $publicNetwork->uuid,
            'name' => $network->name . ' VDC Firewall',
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
            'iaas_network_id'   =>  $network->id
        ]);

        if($vif) {
            MetaHelper::set($vif, 'auto_add_ip_v4', false);

            IpAddressesService::create([
                'iaas_network_id'   =>  $network->id,
                'iaas_virtual_network_card_id'  =>  $vif->id,
                'ip_addr'   =>  '10.128.0.1/32',
            ]);
        }

        $gateway = self::create([
            'name'  =>  $network->name . ' Gateway',
            'iaas_virtual_machine_id'   =>  $firewall->id,
            'gateway_data'  =>  [],
            'gateway_type'  =>  $gatewayType,
            'is_public'  =>  false
        ]);

        dispatch(new Commit($firewall));

        //  Credentials (ssh_username/ssh_password/api_token/api_url) can't be populated
        //  synchronously here - the VM hasn't booted yet. CollectGatewayCredentials polls
        //  until it's reachable, then runs the driver's bootstrap() and writes them back.
        dispatch((new CollectGatewayCredentials($gateway))->delay(now()->addMinutes(3)));

        Log::info(__METHOD__ . " | Provisioned gateway {$gateway->uuid} for network {$network->uuid}");

        return $gateway;
    }

    /**
     * Reachability/API-auth status for a gateway, via its driver's healthCheck().
     */
    public static function getHealth(string $gatewayRef): GatewayHealthStatus
    {
        $gateway = self::getByRef($gatewayRef);

        $driver = app(GatewayDriverManager::class)->requireAdapter($gateway);

        return $driver->healthCheck($gateway);
    }

    /** @return \NextDeveloper\IAAS\ValueObjects\GatewayFirewallRule[] */
    public static function listFirewallRules(string $gatewayRef): array
    {
        return self::firewallRuleDriver($gatewayRef, function ($driver, $gateway) {
            return $driver->listRules($gateway);
        });
    }

    public static function createFirewallRule(string $gatewayRef, array $rule): \NextDeveloper\IAAS\ValueObjects\GatewayFirewallRule
    {
        return self::firewallRuleDriver($gatewayRef, function ($driver, $gateway) use ($rule) {
            return $driver->createRule($gateway, $rule);
        });
    }

    public static function deleteFirewallRule(string $gatewayRef, string $ruleRef): bool
    {
        return self::firewallRuleDriver($gatewayRef, function ($driver, $gateway) use ($ruleRef) {
            return $driver->deleteRule($gateway, $ruleRef);
        });
    }

    /** @return \NextDeveloper\IAAS\ValueObjects\GatewayPortForward[] */
    public static function listPortForwards(string $gatewayRef): array
    {
        return self::natDriver($gatewayRef, function ($driver, $gateway) {
            return $driver->listPortForwards($gateway);
        });
    }

    public static function createPortForward(string $gatewayRef, array $forward): \NextDeveloper\IAAS\ValueObjects\GatewayPortForward
    {
        return self::natDriver($gatewayRef, function ($driver, $gateway) use ($forward) {
            return $driver->createPortForward($gateway, $forward);
        });
    }

    public static function deletePortForward(string $gatewayRef, string $forwardRef): bool
    {
        return self::natDriver($gatewayRef, function ($driver, $gateway) use ($forwardRef) {
            return $driver->deletePortForward($gateway, $forwardRef);
        });
    }

    /**
     * Resolves the gateway + its driver and runs $callback against it, guarding that the
     * driver actually supports firewall-rule management (same instanceof-guard convention
     * as VirtualMachineManager's snapshot/clone capability checks).
     */
    private static function firewallRuleDriver(string $gatewayRef, \Closure $callback)
    {
        $gateway = self::getByRef($gatewayRef);
        $driver = app(GatewayDriverManager::class)->requireAdapter($gateway);

        if (!$driver instanceof FirewallRuleCapableInterface) {
            throw new \Exception("Gateway type {$gateway->gateway_type} does not support firewall rule management");
        }

        return $callback($driver, $gateway);
    }

    /**
     * Same as firewallRuleDriver(), guarding NAT/port-forward capability instead.
     */
    private static function natDriver(string $gatewayRef, \Closure $callback)
    {
        $gateway = self::getByRef($gatewayRef);
        $driver = app(GatewayDriverManager::class)->requireAdapter($gateway);

        if (!$driver instanceof NatCapableInterface) {
            throw new \Exception("Gateway type {$gateway->gateway_type} does not support NAT/port-forward management");
        }

        return $callback($driver, $gateway);
    }
}