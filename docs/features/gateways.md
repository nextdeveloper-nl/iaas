# Gateways

A gateway gives a network its own dedicated firewall appliance — provisioned automatically, reachable with real credentials the moment it boots, and manageable through a self-service firewall-rule and NAT/port-forward API instead of requiring direct access to the appliance's own UI. pfSense CE 2.7.2 is the first supported firewall type; the feature is built as a driver abstraction (`gateway_type`) so additional firewall vendors can be added later without changing any calling code.

## Key Capabilities

- Auto-provision a firewall VM (WAN + LAN NICs, sane defaults) when a network is created, unless `create_gateway: false` is passed
- Explicitly provision a gateway on demand for a network that doesn't have one — never got one automatically, or a previous one was deleted — refusing rather than duplicating if one already exists
- Pick a specific firewall image (`iaas_repository_image_id`) instead of the deployment default, at creation time or when provisioning/replacing a gateway explicitly
- Real, working admin credentials and API access populated automatically after first boot — no manual setup wizard
- Self-service firewall-rule and NAT/port-forward management through our own API, translated per-driver to the underlying appliance's native config
- Clean teardown: deleting a gateway (or the network it belongs to) removes the firewall VM, its NICs/disks, and all gateway records — nothing is left orphaned
- Built to support more than one firewall vendor: `gateway_type` selects a registered driver (see `config/gateway_drivers.php`), mirroring the same pattern already used for hypervisor drivers (see `docs/hypervisor-driver-architecture.md`)

## Architecture

`NextDeveloper\IAAS\Services\Hypervisors\GatewayDriverManager` resolves a `Gateways.gateway_type` value to a driver class implementing `Contracts\GatewayDriverInterface` (bootstrap/healthCheck/applyConfiguration/teardown), with optional `FirewallRuleCapableInterface`/`NatCapableInterface` capabilities a driver can add if its backend supports them. `Services\Hypervisors\PfSense\PfSenseGatewayDriver` is the first concrete driver.

Provisioning logic lives in `GatewaysService::provisionForNetwork()`, shared by two entry points:
- **Implicit** — `Actions\Networks\Create` provisions a gateway automatically for every new network, unless the request explicitly passed `create_gateway: false`.
- **Explicit** — `Actions\Gateways\ProvisionGateway`, dispatched via `POST /iaas/networks/{ref}/do/provision-gateway`, for a network that needs a gateway added after the fact (or replaced — delete the existing one first, see `changes-and-usage.md`).

Credentials can't be known synchronously at provisioning time (the VM hasn't booted yet), so `Jobs\Gateways\CollectGatewayCredentials` is dispatched with a delay, polls until the appliance is reachable, and runs the driver's `bootstrap()` to populate `ssh_username`/`ssh_password`/`ip_addr` on the `Gateways` row - by sending `pfsense.set_password` to the VM's own agent over NATS, not SSH, so no inbound connectivity to the appliance is ever needed - then confirms readiness over NATS the same way (firewall/NAT/health calls go through the same channel, not a REST API or SSH on the box).

## API Examples

**Provision a gateway explicitly for an existing network**

```
POST /iaas/networks/{ref}/do/provision-gateway
```

**Create a firewall rule**

```
POST /iaas/gateways/{ref}/firewall-rules
```
```json
{
  "action": "pass",
  "protocol": "tcp",
  "source": "any",
  "destination": "10.128.0.0/24",
  "port": "443",
  "description": "Allow HTTPS to internal web tier"
}
```

**Create a NAT port-forward**

```
POST /iaas/gateways/{ref}/port-forwards
```
```json
{
  "protocol": "tcp",
  "external_port": 8443,
  "internal_ip": "10.128.0.10",
  "internal_port": 443,
  "description": "Forward to internal web server"
}
```

**Check gateway health**

```
GET /iaas/gateways/{ref}/health
```

## Operational Notes (for whoever manages infrastructure/data outside this codebase)

A few pieces of this feature depend on data or infrastructure this codebase can't manage directly (no migrations, no direct SQL, per project convention):

- **`provision-gateway` action row** — the explicit provisioning endpoint dispatches through the existing generic `AvailableActions` mechanism (same as every other `do/{action}` route). Run `php artisan leo:register-actions` once per environment to create the row (derives `name = provision-gateway`, `input = NextDeveloper\IAAS\Networks`, `class = NextDeveloper\IAAS\Actions\Gateways\ProvisionGateway` from the class itself).
- **`pfsense.agent` on the image** — the pfSense CE `RepositoryImages` catalog entry must ship `pfsense.agent` so it connects to NATS via the config-drive on boot, the same way the Linux/Windows agent does. `bootstrap()` rotates the admin password by sending `pfsense.set_password` to the agent over that same NATS connection now (no SSH, no inbound port); it doesn't install or depend on anything else.
- **`pfsense.agent` NATS operations** — `PfSenseGatewayDriver` assumes the agent supports `pfsense.set_password`, `pfsense.firewall.{list,create,delete}` and `pfsense.nat.{list,create,delete}` (see `pfsense.agent/docs/firewall-api.md`), and that those seven names are in the config-drive's `allowed_operations`.
- **`gateway_data` schema** — currently an open JSON shape (`{rules: [...], nat: [...]}`, consumed by `applyConfiguration()`/`Actions\Gateways\Commit`). Worth pinning down a versioned shape before building UI on top of it.

## Related Features

- [Networking](networking.md) — a network is paired with its gateway via `iaas_gateway_id`
- [Virtual Machines](virtual-machines.md) — a gateway's firewall appliance is itself a VM, provisioned the same way any other VM is
