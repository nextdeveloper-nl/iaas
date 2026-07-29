# Gateways — Required Features

The feature requirements the Gateways implementation is built against, with current
status and where each is implemented. Pairs with `changes-and-usage.md` (what changed
and how to call it), `ui-guide.md` (what to build on top of it), and
`testing-guide.md` (how to verify it). This doc is the checklist: what the feature
*must* do, independent of how the API happens to be shaped today.

## Functional requirements

| # | Requirement | Status | Where |
|---|---|---|---|
| R1 | A network gets a working firewall appliance automatically when created, with no extra step required from the caller. | ✅ Implemented | `Actions\Networks\Create` calls `GatewaysService::provisionForNetwork()` unconditionally unless opted out (R2). |
| R2 | Auto-provisioning can be turned off per network-creation request. | ✅ Implemented | `create_gateway: false` param, threaded from `VdcCreateRequest`/`VirtualDatacenterController` → `VdcServices::createWizard()` → the dispatched action. Default `true`. |
| R3 | A gateway can also be requested explicitly/on demand for a network that doesn't have one yet (opted out earlier, provisioning previously failed, or a previous gateway was deleted). | ✅ Implemented | `POST /iaas/networks/{ref}/do/provision-gateway` → `Actions\Gateways\ProvisionGateway`, sharing the same `provisionForNetwork()` logic. **Needs `php artisan leo:register-actions` run once per environment to seed its `AvailableActions` row** — see "External dependencies" below. |
| R4 | The firewall vendor/type is not hardcoded to pfSense — a second vendor can be added without touching calling code. | ✅ Implemented | `Contracts\GatewayDriverInterface` + `GatewayDriverManager`, keyed off `Gateways.gateway_type`, registered in `config/gateway_drivers.php`. `PfSenseGatewayDriver` is the first (only, so far) driver. |
| R5 | A newly provisioned gateway is reachable with real, working credentials the moment it's ready — no manual pfSense setup wizard. | ✅ Implemented | `Jobs\Gateways\CollectGatewayCredentials` (delayed, retrying job) + `PfSenseGatewayDriver::bootstrap()`, populating `ssh_username`/`ssh_password`/`ip_addr` on the `Gateways` row over SSH, then confirming readiness via a NATS `system.info` call to `pfsense.agent` (see R6-R8; `api_token`/`api_url` are no longer used - firewall/NAT/health go over NATS, not a REST API on the box). |
| R6 | Users can create, list, and delete firewall rules without touching the appliance's own UI. | ✅ Implemented | `GET/POST /iaas/gateways/{ref}/firewall-rules`, `DELETE .../firewall-rules/{rule}` → `GatewaysService` → `FirewallRuleCapableInterface`. No "edit" — delete + recreate by design (see `ui-guide.md`). |
| R7 | Users can create, list, and delete NAT/port-forward rules the same way. | ✅ Implemented | `GET/POST /iaas/gateways/{ref}/port-forwards`, `DELETE .../port-forwards/{forward}` → `GatewaysService` → `NatCapableInterface`. |
| R8 | Reachability/health of a gateway is queryable on demand, not just inferred. | ✅ Implemented | `GET /iaas/gateways/{ref}/health` → `GatewayDriverInterface::healthCheck()`. |
| R9 | Deleting a gateway (directly, or via deleting its network) leaves nothing behind — no orphaned VM, disks, NICs, or DB rows. | ✅ Implemented | `Actions\Gateways\Delete` (driver teardown + VM delete + row cleanup) and `Actions\Networks\Delete` (dispatches the above first). This was the specific bug (`trigger_error()` stubs, wrong model types) that made this pass necessary in the first place. |
| R10 | Credential fields can't be set directly by a normal client — only the system's own bootstrap process, or a privileged admin explicitly attaching an existing VM. | ✅ Implemented | `GatewaysService::create()` strips `ssh_username`/`ssh_password`/`api_token`/`api_url` unless the caller has `datacenter-admin`/`cloud-node-admin`. |
| R11 | A failure anywhere in provisioning (missing image, misconfiguration) is surfaced clearly to whoever's watching the resource — never a silent no-op, never an uncaught fatal error. | ✅ Implemented | `CannotFindAvailableResourceException` replaces the old uncaught `TypeError`; every skip/failure path (`create_gateway: false`, missing image, provisioning exception) leaves a `CommentsService::createSystemComment()` on the network. |
| R12 | A caller can pick which firewall image provisions the gateway, instead of always getting the deployment's config-driven default — both at VDC-creation time and when provisioning/replacing a gateway explicitly. | ✅ Implemented (both entry points) | `iaas_repository_image_id`, threaded from `VdcCreateRequest`/`VirtualDatacenterController` → `VdcServices::createWizard()` → `Actions\Networks\Create`'s `repository_image_id` param, **and** from the explicit `provision-gateway` action's own `iaas_repository_image_id` request field → `Actions\Gateways\ProvisionGateway` — both converge on `GatewaysService::provisionForNetwork()`'s `repository_image_id` override. Must resolve to an `os = 'firewall'` `RepositoryImages` row available on the target cloud node's repositories, or provisioning fails the same way a missing config-driven image does (R11). |
| R13 | Provisioning a gateway for a network that already has a *live* one is refused, not silently duplicated — but a stale reference to an already-deleted gateway/VM doesn't block provisioning forever. | ✅ Implemented | `Actions\Gateways\ProvisionGateway::handle()` checks `iaas_gateway_id` on a fresh load of the network; if set, it looks up the referenced `Gateways` row and its VM (bypassing `AuthorizationScope`/`LimitScope`, `withTrashed()`) to confirm they're actually alive before refusing. If either is gone/soft-deleted, it clears the stale FK and proceeds normally instead of leaving the network stuck. Needed because both old and new firewalls' LAN NIC want the same fixed IP (`10.128.0.1`), and `IpAddressesService::create()` has no uniqueness check — see "Replace an existing gateway's firewall image" in `changes-and-usage.md`. |

## Non-functional requirements

| Requirement | Status | Notes |
|---|---|---|
| **Extensibility** — adding firewall vendor #2 requires no changes to Actions, Controllers, or Services, only a new driver class + config entry. | ✅ Met | Direct structural mirror of the existing hypervisor driver pattern (`docs/hypervisor-driver-architecture.md`) - same reasoning applies. |
| **Zero net-new schema** — the feature fits inside the existing `iaas_gateways` columns. | ✅ Met | `gateway_type`, `gateway_data`, `ssh_*`/`api_*` all pre-existed on the model, previously unused; no migration needed. |
| **Resilience to slow/failed boot** — credential bootstrap tolerates a VM that takes several minutes (or fails) to come up. | ✅ Met | `CollectGatewayCredentials` retries up to 10 times with backoff (60s → 300s, ~27 min total) before giving up. |
| **Auditability** — every automated decision about a gateway (skipped, failed, succeeded) is traceable after the fact without reading logs. | ✅ Met | System comments on the network for every non-happy-path outcome (see `testing-guide.md` for how to query them, including a real caveat about `object_id` storage). |
| **No pfSense-specific code above the driver layer** — Controllers/Services only ever talk to `GatewayDriverManager` and the capability interfaces. | ✅ Met | Confirmed by design: `GatewaysService`'s rule/NAT methods resolve a driver and guard on `instanceof FirewallRuleCapableInterface`/`NatCapableInterface`, never reference pfSense directly. |

## Explicitly out of scope (not required for this iteration)

- **Editing an existing firewall rule or port-forward.** Delete + recreate is the
  supported flow — modeling partial updates against a driver interface that may not
  support them uniformly wasn't worth the complexity yet.
- **A second firewall vendor.** The abstraction exists to make this cheap later, but
  no second driver has been built — `gateway_type` has exactly one registered value
  (`pfsense`) today.
- **HA/failover firewall pairs, VPN/site-to-site configuration, or any config beyond
  basic firewall rules + NAT.** `gateway_data`'s schema is deliberately left open
  (see below) rather than committing to a shape that would need to support these.
- **A defined, versioned schema for `gateway_data`.** It's consumed as an open
  `{rules: [...], nat: [...]}` shape by `applyConfiguration()`/`Actions\Gateways\Commit`
  today; pinning down a real schema is a prerequisite for building anything more
  automated on top of the bulk-apply path, not something this pass needed.

## External dependencies (outside what code alone can deliver)

- **`provision-gateway` `AvailableActions` row** — R3 depends on a DB row that gets
  created by running `php artisan leo:register-actions` (the existing action
  auto-discovery command — not a migration or raw SQL, per project convention) once per
  environment after `Actions\Gateways\ProvisionGateway` ships. It derives
  `name = provision-gateway`, `input = NextDeveloper\IAAS\Networks`,
  `class = NextDeveloper\IAAS\Actions\Gateways\ProvisionGateway` automatically from the
  class itself — no manual DB edit needed, just remembering to run the command.
- **pfSense CE 2.7.2 `RepositoryImages` catalog entry** — R1/R3/R5 all depend on this
  existing and matching `config('leo.iaas.firewalls.pfsense')`. Confirmed already
  registered.
- **`pfsense.agent` NATS operations** — R6/R7/R8 depend on the gateway VM's
  `pfsense.agent` supporting `pfsense.firewall.{list,create,delete}` and
  `pfsense.nat.{list,create,delete}` over NATS (see
  `pfsense.agent/docs/firewall-api.md`) and those six operation names being present in
  `VirtualMachinesMetadataService::buildAgentConfiguration()`'s `allowed_operations` list
  written to the config-drive at provisioning time.
