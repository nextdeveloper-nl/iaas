<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\PfSense;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
 * gateway_type in config/gateway_drivers.php. Talks to pfSense CE's REST API for ongoing
 * rule/NAT management (assumes the community jaredhendrickson13/pfsense-api v2 package -
 * see docs risk notes, this needs confirming against the actual package/version shipped
 * on the provisioned image before going live) and falls back to SSH for first-boot
 * bootstrapping, since the REST API isn't enabled yet on a factory-default appliance.
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
        $apiToken = $gateway->api_token ?: Str::random(48);

        //  pfSense's console shell exposes pfSsh.php for scripted admin operations
        //  (documented, long-standing pfSense feature) - used here to rotate the admin
        //  password away from the factory default and install/enable the REST API
        //  package unattended, so nobody has to walk through the web setup wizard by hand.
        $ssh->exec('pfSsh.php playback changePassword ' . escapeshellarg($generatedPassword));
        $ssh->exec('pkg-static install -y pfSense-pkg-RESTAPI');
        $apiOutput = $ssh->exec('/usr/local/sbin/pfsense-restapi apikey generate --user admin --key ' . escapeshellarg($apiToken));

        $apiUrl = 'https://' . $ipAddr . '/api/v2';

        GatewaysService::update($gateway->uuid, [
            'ip_addr'       => $ipAddr,
            'ssh_username'  => $username,
            'ssh_password'  => $generatedPassword,
            'api_token'     => $apiToken,
            'api_url'       => $apiUrl,
        ]);

        Log::info(__METHOD__ . " | Bootstrapped pfSense gateway {$gateway->uuid} at {$ipAddr}");

        return new GatewayHealthStatus(true, true, 'Bootstrapped', ['api_output' => $apiOutput]);
    }

    public function healthCheck(Gateways $gateway): GatewayHealthStatus
    {
        if (!$gateway->api_url || !$gateway->api_token) {
            return new GatewayHealthStatus(false, false, 'Gateway has no api_url/api_token yet - not bootstrapped.');
        }

        try {
            $response = $this->client($gateway)->get('/status/system');

            return new GatewayHealthStatus(
                reachable: true,
                apiAuthOk: $response->successful(),
                message: $response->successful() ? null : ('API returned HTTP ' . $response->status()),
            );
        } catch (\Throwable $e) {
            return new GatewayHealthStatus(false, false, $e->getMessage());
        }
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
        $response = $this->client($gateway)->get('/firewall/rules');

        return array_map(fn (array $r) => $this->toFirewallRule($r), $response->json('data', []));
    }

    public function createRule(Gateways $gateway, array $rule): GatewayFirewallRule
    {
        $response = $this->client($gateway)->post('/firewall/rule', [
            'type'          => $rule['action'] ?? 'pass',
            'interface'     => $rule['interface'] ?? 'wan',
            'protocol'      => $rule['protocol'] ?? 'any',
            'source'        => $rule['source'] ?? 'any',
            'destination'   => $rule['destination'] ?? 'any',
            'destination_port' => $rule['port'] ?? null,
            'descr'         => $rule['description'] ?? null,
        ]);

        return $this->toFirewallRule($response->json('data', []));
    }

    public function deleteRule(Gateways $gateway, string $ruleRef): bool
    {
        return $this->client($gateway)->delete('/firewall/rule', ['id' => $ruleRef])->successful();
    }

    // -- NatCapableInterface ------------------------------------------------------------

    public function listPortForwards(Gateways $gateway): array
    {
        $response = $this->client($gateway)->get('/firewall/nat/port_forwards');

        return array_map(fn (array $f) => $this->toPortForward($f), $response->json('data', []));
    }

    public function createPortForward(Gateways $gateway, array $forward): GatewayPortForward
    {
        $response = $this->client($gateway)->post('/firewall/nat/port_forward', [
            'protocol'          => $forward['protocol'] ?? 'tcp',
            'destination_port'  => $forward['external_port'] ?? null,
            'target'            => $forward['internal_ip'] ?? null,
            'local_port'        => $forward['internal_port'] ?? null,
            'descr'             => $forward['description'] ?? null,
        ]);

        return $this->toPortForward($response->json('data', []));
    }

    public function deletePortForward(Gateways $gateway, string $forwardRef): bool
    {
        return $this->client($gateway)->delete('/firewall/nat/port_forward', ['id' => $forwardRef])->successful();
    }

    // -- internals ------------------------------------------------------------------

    private function client(Gateways $gateway)
    {
        return Http::baseUrl(rtrim($gateway->api_url, '/'))
            ->withToken($gateway->api_token)
            ->withOptions(['verify' => (bool) ($this->config['verify_tls'] ?? false)])
            ->acceptJson();
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
            ref: (string) ($data['id'] ?? $data['tracker'] ?? ''),
            action: $data['type'] ?? 'pass',
            protocol: $data['protocol'] ?? 'any',
            source: $data['source'] ?? null,
            destination: $data['destination'] ?? null,
            port: $data['destination_port'] ?? null,
            description: $data['descr'] ?? null,
            extra: $data,
        );
    }

    private function toPortForward(array $data): GatewayPortForward
    {
        return new GatewayPortForward(
            ref: (string) ($data['id'] ?? $data['tracker'] ?? ''),
            protocol: $data['protocol'] ?? 'tcp',
            externalPort: (int) ($data['destination_port'] ?? 0),
            internalIp: $data['target'] ?? '',
            internalPort: (int) ($data['local_port'] ?? 0),
            description: $data['descr'] ?? null,
            extra: $data,
        );
    }
}
