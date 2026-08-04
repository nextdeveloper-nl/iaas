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

/**
 * First concrete GatewayDriverInterface implementation, registered under the "pfsense"
 * gateway_type in config/gateway_drivers.php. Everything - bootstrap's one-time admin
 * password rotation included - goes over the NATS connection pfsense.agent already
 * holds (see pfsense.agent/docs/firewall-api.md) via AgentCommandService. No REST API
 * package, and no inbound port (SSH included) needed on the appliance at all: the agent
 * connects out to NATS on its own and runs commands with its own local privileges,
 * which is also why bootstrap() doesn't need to authenticate with any existing/factory
 * credentials before rotating the password.
 */
class PfSenseGatewayDriver implements GatewayDriverInterface, FirewallRuleCapableInterface, NatCapableInterface
{
    /**
     * pfSense CE's default admin account name - assumed for a gateway that hasn't had
     * its credentials rotated yet (no ssh_username recorded on the row).
     */
    private const FACTORY_USERNAME = 'admin';

    public function __construct(private readonly array $config = [])
    {
    }

    // -- GatewayDriverInterface -------------------------------------------------------

    public function bootstrap(Gateways $gateway): GatewayHealthStatus
    {
        $username = $gateway->ssh_username ?: self::FACTORY_USERNAME;
        $generatedPassword = $gateway->ssh_password ?: Str::password(24, symbols: false);

        try {
            //  Runs locally on the box via the agent's own privileges - no inbound
            //  connectivity to the appliance needed (a gateway/firewall may legitimately
            //  never allow that), and no need to authenticate with any existing/factory
            //  password first, unlike the SSH approach this replaced. Also covers
            //  AgentTimeoutException (VM/agent not up yet - the expected state on early
            //  CollectGatewayCredentials attempts - see that class's own retry/backoff),
            //  since agentSend() lets it propagate rather than catching it itself.
            $output = $this->agentSend($gateway, 'pfsense.set_password', [
                'username' => $username,
                'password' => $generatedPassword,
            ]);
        } catch (\Throwable $e) {
            return new GatewayHealthStatus(false, false, 'Could not set admin password: ' . $e->getMessage());
        }

        //  agentSend() only checks the envelope's own status (rejected/failed/completed).
        //  A nil Go-level error can still carry success=false in the operation's own
        //  result (see SetPasswordResult in pfsense.agent) - status is "completed" either
        //  way, so this has to be checked separately.
        if (!($output['success'] ?? false)) {
            return new GatewayHealthStatus(false, false, 'Agent reported password change failure: ' . ($output['message'] ?? 'no message'));
        }

        GatewaysService::update($gateway->uuid, [
            'ip_addr'      => $gateway->ip_addr ?: $this->resolveWanIp($gateway),
            'ssh_username' => $username,
            'ssh_password' => $generatedPassword,
        ]);

        Log::info(__METHOD__ . " | Bootstrapped pfSense gateway {$gateway->uuid}");

        return $this->healthCheck($gateway);
    }

    public function healthCheck(Gateways $gateway): GatewayHealthStatus
    {
        $vm = $gateway->virtualMachines;

        if (!$vm) {
            return new GatewayHealthStatus(false, false, 'Gateway has no underlying VM.');
        }

        try {
            $result = app(AgentCommandService::class)->send('vm', $vm->uuid, 'system.info', [], 10);
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

        $result  = app(AgentCommandService::class)->send('vm', $vm->uuid, $operation, $params, $timeoutS);
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
