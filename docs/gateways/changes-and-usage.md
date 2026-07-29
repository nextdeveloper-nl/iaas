# Gateways — What Changed & How to Use It

This documents the pass that took the Gateways feature from mostly-hollow scaffolding to
a working, self-service pfSense firewall feature, and how to use the result. See
`docs/hypervisor-driver-architecture.md` for the driver pattern this mirrors, and
`docs/features/gateways.md` for the short customer-facing feature summary.

## Why this pass happened

Before this work, "Gateways" existed only as generated CRUD scaffolding:

- `Actions/Gateways/{Create,Delete,Commit}.php` were dead `trigger_error(...)` stubs —
  nothing in the codebase ever called them.
- The only real provisioning path was inlined in `Actions/Networks/Create.php`, fired
  as a side effect of creating a Network on a firewall-enabled cloud node. It built the
  firewall VM and wired up NICs, but never set `ssh_username`/`ssh_password`/
  `api_token`/`api_url` — so nobody could actually log into the box it had just built.
- `Actions/Networks/Delete.php` was *also* a dead stub with the wrong model type.
  Deleting a Network left its firewall VM and `Gateways` row permanently orphaned.
- `GatewaysController::store()` called `GatewaysService::create()` directly, so a raw
  `POST /iaas/gateways` could create a DB row with no VM behind it, and a caller could
  set `ssh_password`/`api_token` themselves.
- `gateway_type` existed on the model but was unused freeform text.

The goal: make a gateway "just work" the moment it's created (real credentials, no
manual pfSense setup wizard), let users manage firewall rules/NAT without touching
pfSense's own UI, and do it in a way that supports more firewall vendors later, not
just pfSense.

## What changed

### 1. Driver abstraction (new)

`gateway_type` is now a real dispatch key, resolved by
`Services\Hypervisors\GatewayDriverManager` to a driver class registered in
`config/gateway_drivers.php` — a direct structural copy of how `VirtualMachineManager`
dispatches hypervisor drivers off `ComputePools.virtualization`.

| File | Purpose |
|---|---|
| `Contracts/GatewayDriverInterface.php` | Mandatory: `bootstrap()`, `healthCheck()`, `applyConfiguration()`, `teardown()` |
| `Contracts/FirewallRuleCapableInterface.php` | Optional: `listRules()`, `createRule()`, `deleteRule()` |
| `Contracts/NatCapableInterface.php` | Optional: `listPortForwards()`, `createPortForward()`, `deletePortForward()` |
| `ValueObjects/GatewayHealthStatus.php` | `{reachable, apiAuthOk, message}` |
| `ValueObjects/GatewayFirewallRule.php` | Driver-agnostic rule shape |
| `ValueObjects/GatewayPortForward.php` | Driver-agnostic NAT/port-forward shape |
| `Services/Hypervisors/GatewayDriverManager.php` | Registry: `gateway_type` → driver class |
| `Services/Hypervisors/PfSense/PfSenseGatewayDriver.php` | First concrete driver (pfSense) |
| `config/gateway_drivers.php` | `platforms.pfsense.driver => PfSenseGatewayDriver::class` |

Adding a second firewall vendor later means: write a new driver class implementing
`GatewayDriverInterface` (+ whichever capability interfaces it supports), register it
in `config/gateway_drivers.php` under a new `gateway_type` key, and add its image
triple to `leo.iaas.firewalls.<type>` in the consuming app's config. Nothing else in
this codebase needs to change.

### 2. Fixed the lifecycle

- **`GatewaysService::provisionForNetwork(Networks $network, array $overrides = [])`**
  (new) — the extracted, shared provisioning logic (was inlined in
  `Actions/Networks/Create.php`). Resolves the cloud node, the image from
  `leo.iaas.firewalls.<gateway_type>` (or from `overrides['repository_image_id']` if
  given — see below), builds the firewall VM + WAN/LAN NICs, creates the `Gateways`
  row, dispatches the VM commit, and schedules credential collection. Whether it's
  called at all is gated by the caller (`Actions\Networks\Create`'s `create_gateway`
  param, default `true`) - not by the cloud node itself.
- **`overrides['repository_image_id']` (new)** — lets a caller pin the gateway to a
  specific `RepositoryImages` row instead of the config-driven os/distro/version
  lookup. Must be an `os = 'firewall'` image belonging to a repository attached to the
  network's cloud node, or `provisionForNetwork()` throws
  `CannotFindAvailableResourceException` the same way a missing config-driven image
  does. Threaded through from both provisioning entry points: `VdcServices::createWizard()`'s
  `$repositoryImageId` param (see "Get a gateway automatically" below) via
  `Actions\Networks\Create`'s `repository_image_id` action param, and the explicit
  `Actions\Gateways\ProvisionGateway` action's `iaas_repository_image_id` request field
  (see "Provision a gateway explicitly" below).
- **`Actions/Networks/Create.php`** — shrunk to: switch config, then call
  `provisionForNetwork()`. Same observable behavior as before, just de-duplicated.
- **`Actions/Gateways/ProvisionGateway.php`** (renamed from `Create.php` to avoid an
  `AvailableActions` name collision — both this class and `Actions\Networks\Create` take
  a `Networks $network` first constructor param, so both would auto-derive
  `name=create` from `leo:register-actions`, the tool that seeds the `AvailableActions`
  table used by the generic `do/{action}` dispatcher) — now the *explicit* entry point.
  Takes a `Networks $network` (was wrongly typed `VirtualMachines` before), calls the
  same `provisionForNetwork()`, optionally passing a `gateway_type` and/or
  `iaas_repository_image_id` from action params. Refuses to run if the network already
  has a *live* gateway (see "Provision a gateway explicitly" below), but self-heals a
  stale `iaas_gateway_id` (one pointing at an already-deleted gateway/VM) instead of
  refusing forever. Both writes to `Networks` (the self-heal, and the final
  `iaas_gateway_id` assignment once provisioning succeeds) run inside
  `UserHelper::runAsAdmin()`: this action runs on the `iaas` queue - a separate worker
  process with no authenticated session of its own - and an instance `->update()` fires
  `NetworksObserver`'s `saving()`/`updating()` hooks, which run a `UserHelper::can()`
  authorization check that has nothing to authorize against there, failing with "You
  are not allowed to save this record" otherwise.
- **`Actions/Gateways/Delete.php`** — now real: best-effort driver `teardown()`,
  unlinks `Networks.iaas_gateway_id` (bypassing `AuthorizationScope`/`LimitScope` -
  this write previously used the plain scoped query, which could silently match zero
  rows and leave the FK stuck if this action ran somewhere `AuthorizationScope`
  couldn't resolve a role, e.g. an async queue worker without a re-established
  user/account context), dispatches the existing (already fully implemented)
  `Actions\VirtualMachines\Delete` on the underlying VM, then removes the `Gateways`
  row.
- **`Actions/Networks/Delete.php`** — fixed to take `Networks $network` (was wrongly
  typed), and now dispatches `Actions\Gateways\Delete` first if the network has one,
  closing the orphaned-VM leak.
- **`Actions/Gateways/Commit.php`** — now real: pushes `gateway_data` to the live
  appliance via the driver's `applyConfiguration()`. Distinct from
  `Actions\VirtualMachines\Commit`, which is unrelated and unchanged. Individual
  rule/NAT changes (below) go through the driver directly and synchronously — this
  action is for bulk resync/drift-repair, not the normal per-rule path.

### 3. Automatic credentials — no manual pfSense setup wizard

`Jobs/Gateways/CollectGatewayCredentials` is dispatched (with a delay) right after a
gateway is provisioned. It polls until the appliance is reachable, then runs
`PfSenseGatewayDriver::bootstrap()`, which:

- SSHes in with the factory-default pfSense credentials (or whatever's already on the
  `Gateways` row, if this isn't the first attempt),
- rotates the admin password to a generated one via pfSense's `pfSsh.php playback
  changePassword` shell command,
- writes `ssh_username`, `ssh_password`, and `ip_addr` back onto the `Gateways` row via
  `GatewaysService::update()`,
- then confirms readiness the same way `healthCheck()` does: a `system.info` command
  sent over NATS to the gateway VM's `pfsense.agent`.

Firewall/NAT/health no longer go through a REST API on the box - `pfsense.agent`
connects to NATS the same way any other VM agent does (via the config-drive, not
anything `bootstrap()` sets up), so there's no API package to install or token to
generate anymore. `api_token`/`api_url` on the `Gateways` row are no longer populated by
this driver.

If the VM isn't booted yet, `bootstrap()` reports `reachable: false` and the job
retries with backoff (10 attempts, backing off from 60s up to 300s) rather than
failing outright.

### 4. Self-service firewall-rule / NAT API (new)

All new endpoints route through `GatewaysService` → `GatewayDriverManager` → the
gateway's own driver — nothing above the driver layer is pfSense-specific.

| Method | Route | Purpose |
|---|---|---|
| `GET` | `/iaas/gateways/{ref}/health` | Reachability + API-auth status |
| `GET` | `/iaas/gateways/{ref}/firewall-rules` | List firewall rules |
| `POST` | `/iaas/gateways/{ref}/firewall-rules` | Create a firewall rule |
| `DELETE` | `/iaas/gateways/{ref}/firewall-rules/{rule}` | Delete a firewall rule |
| `GET` | `/iaas/gateways/{ref}/port-forwards` | List NAT port-forwards |
| `POST` | `/iaas/gateways/{ref}/port-forwards` | Create a NAT port-forward |
| `DELETE` | `/iaas/gateways/{ref}/port-forwards/{forward}` | Delete a NAT port-forward |

Backed by `GatewaysService::{getHealth,listFirewallRules,createFirewallRule,
deleteFirewallRule,listPortForwards,createPortForward,deletePortForward}()`. Each
throws a clear error if the resolved driver doesn't implement the relevant capability
interface (e.g. a future firewall type that doesn't support NAT management).

### 5. Locked down direct gateway creation

`GatewaysService::create()` now strips `ssh_username`/`ssh_password`/`api_token`/
`api_url` from the incoming data unless the caller has the `datacenter-admin` or
`cloud-node-admin` role — mirrors the same role-gated field-stripping already used in
`VirtualMachineServices::createWizard()`. `POST /iaas/gateways` itself was **not**
removed (to avoid breaking existing route consumers); it's now effectively an
admin-only "attach an existing VM as a gateway" path. Normal usage should go through
provisioning (below), not direct creation.

## How to use it

### Get a gateway automatically

Nothing to do — create a VDC as usual:

```
POST /iaas/virtual-datacenters
```
```json
{
  "name": "prod-network",
  "iaas_cloud_node_id": "..."
}
```

A gateway is provisioned automatically and linked via the network's
`iaas_gateway_id`, unless the request set `"create_gateway": false`.

To pin the gateway to a specific firewall image instead of the deployment default
(`leo.iaas.firewalls.<default_firewall_type>`), pass its `RepositoryImages` uuid:

```json
{
  "name": "prod-network",
  "iaas_cloud_node_id": "...",
  "iaas_repository_image_id": "<uuid of an os=firewall RepositoryImages row>"
}
```

`iaas_repository_image_id` is optional and nullable — omit it to keep the existing
config-driven default behavior. It must reference an `os = 'firewall'` image
available on the target cloud node's own repositories; otherwise provisioning fails
the same way a missing config-driven image does (see "Missing image error path" in
`testing-guide.md`) - the VDC itself is still created, only the gateway is affected.
List candidate images with `GET /iaas/repository-images?filter[os]=firewall`.

### Provision a gateway explicitly

For a network that doesn't currently have one — never got one automatically
(`create_gateway: false` was set, or provisioning failed), or had a previous one
deleted — or to pick a different `gateway_type`/firewall image than the deployment
default:

```
POST /iaas/networks/{ref}/do/provision-gateway
```
```json
{
  "gateway_type": "pfsense",
  "iaas_repository_image_id": "<uuid of an os=firewall RepositoryImages row>"
}
```

Both fields are optional — omit `gateway_type` to use `leo.iaas.default_firewall_type`,
omit `iaas_repository_image_id` to use the config-driven image lookup for that type.
Same constraints as the wizard's version of this field (see "Get a gateway
automatically" above): must be an `os = 'firewall'` image available on the network's
own cloud node.

If the network **already has a live gateway**, this is refused (a system comment is
left on the network, the action ends in an error state) rather than silently
provisioning a second one — delete the existing gateway first (see "Delete a gateway"
below). "Live" means the referenced `Gateways` row and its underlying VM both actually
still exist and aren't lost/soft-deleted — if `iaas_gateway_id` is a stale leftover
from an already-torn-down gateway, this clears it and provisions normally instead of
refusing forever.

> **Requires a manual step outside this codebase**: this dispatches through the
> generic `do/{action}` mechanism, which looks up the action by name in the
> `AvailableActions` table (managed externally, same as schema — no seeder exists in
> this repo). A row needs to exist with `name = provision-gateway`,
> `input = NextDeveloper\IAAS\Networks`, `class = NextDeveloper\IAAS\Actions\Gateways\ProvisionGateway`
> before this endpoint will do anything — run `php artisan leo:register-actions` (the
> existing auto-discovery command) once per environment after this ships to create it;
> it derives that exact name/input/class from the class itself, no manual DB edit
> needed.

### Replace an existing gateway's firewall image

There's no atomic "swap" operation — replacing a gateway's firewall image is delete the
old one, then provision a new one with the image you want, same as changing a VM's
underlying image anywhere else in this codebase:

```
DELETE /iaas/gateways/{gateway_id}
```

Wait for the delete to fully finish (poll `GET /iaas/networks/{ref}` until
`iaas_gateway_id` is `null` again — deletion tears down the old firewall VM/disks/NICs,
see "Delete a gateway" below), then:

```
POST /iaas/networks/{ref}/do/provision-gateway
```
```json
{
  "iaas_repository_image_id": "<uuid of the new firewall image>"
}
```

Calling `provision-gateway` before the delete has finished tearing down the old gateway
is refused by the guard described above — both the old and new firewall's LAN NIC want
the same fixed IP (`10.128.0.1`), so this isn't safe to race. Once the old gateway's VM
is actually gone, the guard detects the stale `iaas_gateway_id` and self-heals rather
than requiring a manual `PATCH /iaas/networks/{ref}` to clear it.

### Wait for credentials, then check health

Credentials aren't available immediately — the VM has to boot first. Poll:

```
GET /iaas/gateways/{ref}/health
```
```json
{
  "data": {
    "reachable": true,
    "api_auth_ok": true,
    "message": null
  }
}
```

Once `api_auth_ok` is `true`, `ssh_username`/`ssh_password`/`api_token`/`api_url` are
populated on `GET /iaas/gateways/{ref}` (visible to whoever can read that gateway —
these aren't stripped on reads, only on writes from non-privileged callers).

### Manage firewall rules

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
```
GET    /iaas/gateways/{ref}/firewall-rules
DELETE /iaas/gateways/{ref}/firewall-rules/{rule}
```

`action` accepts `pass`, `block`, or `reject`. `rule` in the delete path is the value
returned as `ref` on each listed rule — pass it back untouched, it's the driver's own
identifier, not something to construct yourself.

### Manage NAT port-forwards

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
```
GET    /iaas/gateways/{ref}/port-forwards
DELETE /iaas/gateways/{ref}/port-forwards/{forward}
```

Same `ref`-passthrough rule as firewall rules applies to `forward`.

### Delete a gateway

```
DELETE /iaas/gateways/{ref}
```

Tears down the firewall VM (disks, NICs, IPs — via the existing, already-robust
`Actions\VirtualMachines\Delete`), clears the owning network's `iaas_gateway_id`, and
removes the `Gateways` row. Deleting the network itself
(`DELETE /iaas/networks/{ref}`) does the same gateway cleanup automatically first.

## Known gaps / things to confirm before relying on this in production

- **`pfsense.agent` NATS operations** — `PfSenseGatewayDriver` now assumes the gateway
  VM's `pfsense.agent` supports `pfsense.firewall.{list,create,delete}` and
  `pfsense.nat.{list,create,delete}` (see `pfsense.agent/docs/firewall-api.md`), and
  that those six names are in the config-drive's `allowed_operations` (added in
  `VirtualMachinesMetadataService::buildAgentConfiguration()`). No REST API package or
  bearer token is used anymore.
- **`AvailableActions` row** for `provision-gateway` needs to be added externally (see
  above) — not something a code change can deliver.
- **`gateway_data` schema** is currently an open JSON shape (`{rules: [...], nat:
  [...]}`). Worth pinning down a versioned shape before building UI or automation on
  top of `Actions\Gateways\Commit`'s bulk-apply path.
- **`RepositoryImages.post_boot_script`** for the pfSense image is optional but
  recommended — without it, `bootstrap()` still works purely over SSH, just slightly
  slower on first boot.
