<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\PfSense;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Events\Exceptions\AgentTimeoutException;
use NextDeveloper\Events\Services\AgentCommandService;
use NextDeveloper\IAAS\Contracts\FirewallRuleCapableInterface;
use NextDeveloper\IAAS\Contracts\GatewayDriverInterface;
use NextDeveloper\IAAS\Contracts\NatCapableInterface;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Services\GatewaysService;
use NextDeveloper\IAAS\ValueObjects\GatewayFirewallRule;
use NextDeveloper\IAAS\ValueObjects\GatewayHealthStatus;
use NextDeveloper\IAAS\ValueObjects\GatewayPortForward;
use phpseclib3\Net\SSH2;

/**
 * First concrete GatewayDriverInterface implementation, registered under the "pfsense"
 * gateway_type in config/gateway_drivers.php. Firewall-rule/NAT management and health
 * checks go over the NATS connection pfsense.agent already holds (see
 * pfsense.agent/docs/firewall-api.md) via AgentCommandService - no REST API package or
 * inbound port needed on the appliance. SSH is used only for bootstrap()'s one-time
 * admin password rotation.
 */
class PfSenseGatewayDriver implements GatewayDriverInterface, FirewallRuleCapableInterface, NatCapableInterface
{
    /**
     * Factory-default pfSense CE credentials - public, documented defaults (not a secret),
     * used only to reach a freshly booted appliance before bootstrap() rotates them.
     */
    private const FACTORY_USERNAME = 'admin';
    private const FACTORY_PASSWORD = 'pfsense';

    public function __construct(private readonly array $config = [])
    {
    }

    // -- GatewayDriverInterface -------------------------------------------------------

    public function bootstrap(Gateways $gateway): GatewayHealthStatus
    {
        $ipAddr = $gateway->ip_addr ?: $this->resolveWanIp($gateway);

        if (!$ipAddr) {
            return new GatewayHealthStatus(false, false, 'Gateway VM has no WAN IP yet - not booted, or DHCP lease not assigned.');
        }

        try {
            $ssh = new SSH2($ipAddr, 22, 15);
        } catch (\Throwable $e) {
            return new GatewayHealthStatus(false, false, 'Could not open SSH connection: ' . $e->getMessage());
        }

        $username = $gateway->ssh_username ?: self::FACTORY_USERNAME;
        $password = $gateway->ssh_password ?: self::FACTORY_PASSWORD;

        if (!$ssh->login($username, $password)) {
            return new GatewayHealthStatus(false, false, 'SSH authentication failed with configured/factory credentials.');
        }

        $generatedPassword = $gateway->ssh_password ?: Str::password(24, symbols: false);

        //  pfSense's console shell exposes pfSsh.php for scripted admin operations
        //  (documented, long-standing pfSense feature) - used here to rotate the admin
        //  password away from the factory default so nobody has to walk through the web
        //  setup wizard by hand. Firewall/NAT management and health no longer go through
        //  a REST API on the box - they go over the NATS connection pfsense.agent already
        //  holds (see healthCheck()/listRules()/etc below), so there's nothing else to
        //  provision here.
        $ssh->exec('pfSsh.php playback changePassword ' . escapeshellarg($generatedPassword));

        GatewaysService::update($gateway->uuid, [
            'ip_addr'      => $ipAddr,
            'ssh_username' => $username,
            'ssh_password' => $generatedPassword,
        ]);

        Log::info(__METHOD__ . " | Bootstrapped pfSense gateway {$gateway->uuid} at {$ipAddr}");

        return $this->healthCheck($gateway);
    }

    public function healthCheck(Gateways $gateway): GatewayHealthStatus
    {
        $vm = $gateway->virtualMachines;

        if (!$vm) {
            return new GatewayHealthStatus(false, false, 'Gateway has no underlying VM.');
        }

        try {
            $result = app(AgentCommandService::class)->send($vm->uuid, 'system.info', [], 10);
        } catch (AgentTimeoutException $e) {
            return new GatewayHealthStatus(false, false, 'Agent did not respond: ' . $e->getMessage());
        }

        $status = $result['payload']['status'] ?? 'failed';

        return new GatewayHealthStatus(
            reachable: true,
            apiAuthOk: $status === 'completed',
            message: $status === 'completed' ? null : ($result['payload']['message'] ?? 'system.info failed'),
        );
    }

    public function applyConfiguration(Gateways $gateway): void
    {
        $desired = $gateway->gateway_data ?? [];

        foreach ($desired['rules'] ?? [] as $rule) {
            $this->createRule($gateway, $rule);
        }

        foreach ($desired['nat'] ?? [] as $forward) {
            $this->createPortForward($gateway, $forward);
        }
    }

    public function teardown(Gateways $gateway): void
    {
        //  No controller/license to deregister from for a standalone pfSense CE
        //  appliance - the VM teardown that follows this call is sufficient.
    }

    // -- FirewallRuleCapableInterface ---------------------------------------------------

    public function listRules(Gateways $gateway): array
    {
        $output = $this->agentSend($gateway, 'pfsense.firewall.list');

        return array_map(fn (array $r) => $this->toFirewallRule($r), $output['rules'] ?? []);
    }

    public function createRule(Gateways $gateway, array $rule): GatewayFirewallRule
    {
        $params = [
            'interface'        => $rule['interface'] ?? 'wan',
            'action'           => $rule['action'] ?? 'pass',
            'protocol'         => $rule['protocol'] ?? null,
            'source'           => $rule['source'] ?? 'any',
            'destination'      => $rule['destination'] ?? 'any',
            'destination_port' => $rule['port'] ?? null,
            'description'      => $rule['description'] ?? null,
        ];

        //  pfsense.firewall.create only returns the new tracker, not the full rule - build
        //  the value object from what we sent rather than re-parsing the output.
        $output = $this->agentSend($gateway, 'pfsense.firewall.create', $params);

        return $this->toFirewallRule(['tracker' => $output['tracker'] ?? null] + $params);
    }

    public function deleteRule(Gateways $gateway, string $ruleRef): bool
    {
        $this->agentSend($gateway, 'pfsense.firewall.delete', ['tracker' => $ruleRef]);

        return true;
    }

    // -- NatCapableInterface ------------------------------------------------------------

    public function listPortForwards(Gateways $gateway): array
    {
        $output = $this->agentSend($gateway, 'pfsense.nat.list');

        return array_map(fn (array $f) => $this->toPortForward($f), $output['port_forwards'] ?? []);
    }

    public function createPortForward(Gateways $gateway, array $forward): GatewayPortForward
    {
        $params = [
            'interface'        => $forward['interface'] ?? 'wan',
            'protocol'         => $forward['protocol'] ?? 'tcp',
            'destination_port' => $forward['external_port'] ?? null,
            'target_ip'        => $forward['internal_ip'] ?? null,
            'target_port'      => $forward['internal_port'] ?? null,
            'description'      => $forward['description'] ?? null,
        ];

        //  pfsense.nat.create only returns the new tracker, not the full forward - build
        //  the value object from what we sent rather than re-parsing the output.
        $output = $this->agentSend($gateway, 'pfsense.nat.create', $params);

        return $this->toPortForward(['tracker' => $output['tracker'] ?? null] + $params);
    }

    public function deletePortForward(Gateways $gateway, string $forwardRef): bool
    {
        $this->agentSend($gateway, 'pfsense.nat.delete', ['tracker' => $forwardRef]);

        return true;
    }

    // -- internals ------------------------------------------------------------------

    /**
     * Sends a pfsense.* operation to the gateway's VM agent over NATS and unwraps the
     * result envelope's payload, throwing when the agent reports "rejected" (operation
     * not allowlisted) or "failed" (bad params/pfSense-side error) - see
     * pfsense.agent/docs/firewall-api.md. AgentTimeoutException is left to propagate.
     */
    private function agentSend(Gateways $gateway, string $operation, array $params = [], int $timeoutS = 10): array
    {
        $vm = $gateway->virtualMachines;

        if (!$vm) {
            throw new \Exception("Gateway {$gateway->uuid} has no underlying VM to reach its agent.");
        }

        $result  = app(AgentCommandService::class)->send($vm->uuid, $operation, $params, $timeoutS);
        $payload = $result['payload'] ?? [];
        $status  = $payload['status'] ?? 'failed';

        if ($status !== 'completed') {
            throw new \Exception("pfSense agent operation '{$operation}' {$status}: " . ($payload['message'] ?? 'no message'));
        }

        return $payload['output'] ?? [];
    }

    private function resolveWanIp(Gateways $gateway): ?string
    {
        $vm = $gateway->virtualMachines;

        if (!$vm) {
            return null;
        }

        $wanNic = $vm->virtualNetworkCards()->orderBy('device_number')->first();

        $ip = $wanNic?->ipAddresses()->first()?->ip_addr;

        return $ip ? explode('/', $ip)[0] : null;
    }

    private function toFirewallRule(array $data): GatewayFirewallRule
    {
        return new GatewayFirewallRule(
            ref: (string) ($data['tracker'] ?? ''),
            action: $data['action'] ?? 'pass',
            protocol: $data['protocol'] ?? 'any',
            source: $data['source'] ?? null,
            destination: $data['destination'] ?? null,
            port: $data['destination_port'] ?? null,
            description: $data['description'] ?? null,
            extra: $data,
        );
    }

    private function toPortForward(array $data): GatewayPortForward
    {
        return new GatewayPortForward(
            ref: (string) ($data['tracker'] ?? ''),
            protocol: $data['protocol'] ?? 'tcp',
            externalPort: (int) ($data['destination_port'] ?? 0),
            internalIp: $data['target_ip'] ?? '',
            internalPort: (int) ($data['target_port'] ?? 0),
            description: $data['description'] ?? null,
            extra: $data,
        );
    }
}
