# IAAS Multi-Hypervisor Architecture Redesign

Status: Phase 0 partially landed (scaffold fixes, `XenServer82SshDriver`, event pipeline
normalization, and the highest-traffic call sites migrated onto `VirtualMachineManager`).
Remaining Phase 0 surface (`Commit`/`HealthCheck`/`ConvertToTemplate`/`Export`/
`ExportAsMachineImage`/`Delete`/`MountCd`/`EjectCd`/`UpdateConfigurationIso`, Jobs, Console
commands, Controllers) and Phases 1-4 (XCP-ng/Proxmox/VMware/KVM) not started yet.

## Context

The IAAS vendor package (`NextDeveloper\IAAS`) today supports exactly one hypervisor
backend — XenServer — reached entirely via direct SSH + `xe` CLI text-scraping from the
Laravel app. The business requirement is to support five backends: **XenServer, XCP-ng,
VMware, Proxmox, and KVM**. A hard operational constraint shapes every part of this
design: **there is very little room to change the database schema** — this project's
standing rule is no Laravel migrations, no raw SQL; schema changes go through a separate,
slow, careful process outside normal dev work. So the design's job is not just "add four
hypervisors" but "add four hypervisors while touching the schema as close to zero times as
possible," reusing what already exists.

Deep research (three parallel codebase explorations + one architecture-drafting pass, all
summarized below) found the codebase is in a better starting position than it looks:
- A **dispatch column already exists and is already used this way** (`ComputePools.virtualization`), following the exact pattern this same codebase already shipped for switches (`NetworkMembers.switch_type` → `Services/Switches/DellS6100.php`).
- A **driver-registry abstraction was already started and abandoned** (`src/Contracts/*Interface.php` + `src/Services/HypervisorsV2/VirtualMachineManager.php`) — unused today because of two confirmed, fixable bugs, not because the pattern was wrong.
- A **zero-migration extensibility mechanism is already in production use** (`Meta`/`MetaHelper`, a polymorphic key→JSON-value table), plus several already-existing JSON blob columns (`management_data`, `hypervisor_data`, `connection_parameters`) sized for exactly this kind of driver-specific payload.
- A **team-authored design doc already exists** (`IAAS/docs/v2-agent-architecture.md`) proposing to replace SSH with a NATS-based host agent, mirroring the already-shipped in-guest VM agent. This document treats that doc as prior art for the *transport* layer and deliberately designs the *driver-abstraction* layer to be decoupled from it, so hypervisor #2–#5 don't have to wait on the agent rollout finishing.

The team confirmed, when this design was reviewed: hypervisor hosts are **not** all
self-owned (some VMware/Proxmox environments may be customer/third-party managed,
API-credential-only), and transport should **support both** agent-based and
native-API-based approaches per backend rather than picking one uniformly — e.g. a
`vmware-rest` driver and a `vmware-agent` driver can coexist, selected per deployment.
Rollout order confirmed: XenServer → XCP-ng → Proxmox → VMware → KVM.

---

## 1. Target Layering

### 1.1 Fix, don't replace, the existing scaffold

`src/Contracts/` + `src/Services/HypervisorsV2/VirtualMachineManager.php` already contain a driver-registry skeleton: a base `VirtualMachineAdapterInterface` (start/stop/restart/pause/resume/suspend/getHypervisorData/delete/sync/listAll), optional capability interfaces (`SnapshotCapableInterface`, `CloneCapableInterface`, `ResizeCapableInterface`), and a registry (`registerAdapter(string $platform, string $adapterClass)` / `getAdapter(VirtualMachines $vm)` keyed by `$computePool->virtualization`, reading driver config from `config("virtualization.platforms.{$platform}")`). It was unused before Phase 0 — confirmed dead via grep, no service-provider binding, no config file — because of two concrete bugs (both fixed in Phase 0):

1. **Namespace-casing typo.** `composer.json`'s PSR-4 map is `"NextDeveloper\\IAAS\\": "src/"` (capital D). Four of five files in `src/Contracts/` declared `namespace Nextdeveloper\IAAS\Contracts;` (lowercase d) — an autoload-breaking typo, not an intentional alternate namespace.
2. **`$vm->platform` doesn't exist.** `VirtualMachineManager.php` referenced `$vm->platform` in two error messages. `VirtualMachines` has no `platform` property — the actual dispatch key is `VirtualMachinesService::getComputePool($vm)->virtualization`.

Two more bugs were found while completing the scaffold: `VirtualMachines::updateState()` wrote to a `state` column that doesn't exist (real column is `status`), and `ResizeCapableInterface::resize()` had a copy-pasted `string $newName` parameter from `CloneCapableInterface` instead of cpu/ram. The dead, unreferenced `XenVmManager` (which also didn't satisfy its own interface — `stop()`/`restart()` returned `bool` where `VirtualMachineAdapterInterface` requires `VirtualMachines`) was removed rather than fixed.

Everything else about the scaffold — the registry pattern, the base interface, the optional-capability-interface pattern — was structurally correct and has been completed, not discarded.

### 1.2 New capability interfaces needed

The original three capability interfaces (Snapshot/Clone/Resize) covered only a slice of the full operation surface. Added in Phase 0, all optional (a driver implements only what its backend/transport supports; callers `instanceof`-check before invoking, exactly like the existing Snapshot/Clone pattern):

- **`ConsoleCapableInterface`** — `getConsoleUrl(VirtualMachines $vm): ConsoleSession` (a small value object carrying URL + ticket/token + protocol — XenServer/VMware/Proxmox/KVM all hand back materially different things). Console access was previously two hardcoded XenServer-only methods on `VirtualMachinesService`, picked by controller route, not by driver dispatch.
- **`MigrationCapableInterface`** — not created in Phase 0. Mirrors the existing 10-step `MigrationInterface.php` contract (`preFlightChecks` → ... → `run`), which is sound and already fairly hypervisor-neutral in its method signatures; today it's directly instantiated by console commands/controllers instead of dispatched through any interface. **Deliberately deferred** — see §6/§7: migration is a large, high-value feature in its own right and the team confirmed it can be picked up later as its own initiative.
- **`DiskCapableInterface`** — create/attach/detach/destroy/resize + CD mount/eject. `src/Actions/VirtualDiskImages/{Attach,Create}.php` (previously unimplemented `trigger_error(...)` stubs) are now built against it; `Copy.php` remains unimplemented since no underlying disk-copy operation exists anywhere in this codebase to wrap.
- **`NetworkCapableInterface`** — VIF-equivalent create/destroy/param-set/get, IP allow-list, and an intent-level `setIpFilterMode(locked|unlocked|disabled|default)` that each driver maps to its own native vocabulary (XenServer's `locking-mode` enum today).
- **`HostSyncInterface`** — scoped to `ComputeMembers`: discover hypervisor type+version, sync host interfaces/networks/storage volumes, host CPU/memory info. Replaces `HypervisorService::getHypervisor()`'s `lsb_release`-string-matching dead end for dispatch purposes (the method itself is still used internally to populate `hypervisor_model` for display). **Also detects and records pool membership mode** — confirmed by the team: some deployments have hosts joined to a real XenServer/XCP-ng native pool (`pool-join`), others use only the platform's own virtual/logical `ComputePools` grouping with hosts never joining a real hypervisor-native pool, and this is not converging toward one model (see §7).
- **`EventTranslatorCapableInterface`** — `translate(mixed $rawEvent): NormalizedHypervisorEvent` (see §5).

The base `VirtualMachineAdapterInterface` stays mandatory; capability interfaces stay optional, so a driver can ship with partial coverage without blocking on everything at once. `XenServer82SshDriver::suspend()` and `::listAll()` currently throw (no underlying XenService operation to wrap for either) — both are mandatory interface methods with a documented gap rather than a capability interface, a known limitation to revisit.

### 1.3 Dispatch key: one column, extended to encode transport variants

Two dispatch keys existed and are being consolidated into one. `ComputePools.virtualization` is the dispatch key in the newest code (`src/Actions/VirtualMachines/Commit.php`'s `switch ($this->computePool->virtualization) {...}` blocks, `src/Actions/VirtualDiskImages/{Destroy,Sync,Resize}.php`). `ComputeMembers.hypervisor_model` (free text like `"XenServer 8.2"`, derived from `lsb_release` string-matching) was a second, more fragile dispatch key used more widely than expected: `ComputeMembers/Initiate.php`, `StorageVolumes/Scan.php`, `GarbageCollectors/CollectGarbageNetworks.php` (six brittle version-string cases), `Services/Repositories/ImageSupportService.php`, `VirtualMachines/{ExportAsMachineImage,ConvertToTemplate}.php`, `Services/HypervisorsV2/EvacuationService.php`.

`ComputePools.virtualization` is now the single source of truth for driver dispatch (VM/host/disk/network level) in every migrated call site. `hypervisor_model` stays on the model as free-text display/diagnostic info (populated by `XenServer82SshDriver::detectVersion()`), it just stops being read for branching in migrated code. **Not yet migrated**: `ExportAsMachineImage.php`/`ConvertToTemplate.php`/`ImageSupportService.php` still read `hypervisor_model` — but there it's used as an image-compatibility tag written into `RepositoryImages.supported_virtualizations` (a persisted `text[]` column), a data-format question deliberately deferred (§7.8), not a simple dispatch swap. `EvacuationService.php`'s reads are descriptive snapshot data, not branching, and were left alone.

**Transport-variant naming:** transport is encoded into the `virtualization` string as an explicit suffix, so the single string column remains the complete dispatch key with zero schema change, while still letting an operator pick per-compute-pool which transport a given deployment uses:

```
xenserver-8.2           (legacy bare value - every existing ComputePools row has this today, registered as an alias)
xenserver-8.2-ssh       (current/default - wraps existing SSH+xe code, Phase 0)
xenserver-8.2-agent     (later - NATS agent transport, per v2-agent-architecture.md)
xcp-ng-8.2 / xcp-ng-8.2-ssh
xcp-ng-8.2-agent
proxmox-8-rest          (native PVE API, token auth)
proxmox-8-agent
vmware-8.0-rest         (native vSphere REST API)
vmware-8.0-agent
kvm-agent                (no viable native-API-only path - see §3)
```

**Important:** `config/virtualization.php` registers both `xenserver-8.2` (bare, matching what every existing `ComputePools` row actually has stored) and `xenserver-8.2-ssh` (the new explicit convention) as aliases for the same driver class — this was caught during Phase 0 implementation as a real production-breaking gap (the registry would have thrown `AdapterNotFoundException` for every existing compute pool otherwise).

Each fully-qualified string maps, via config (§1.4), to its own driver class — e.g. a future `VmwareRestDriver` and `VmwareAgentDriver` would both implement the same `VirtualMachineAdapterInterface` + capability interfaces, differing only in how they execute the operations.

### 1.4 Config-driven registration

`config/virtualization.php` maps each `virtualization` string to its driver class. Registration happens once in `IAASServiceProvider::registerHypervisorDrivers()`, binding `VirtualMachineManager` as a singleton and iterating the config to call `registerAdapter()` — not scattered `registerAdapter()` calls elsewhere. The manager is also aliased as `vm.manager` for the `NextDeveloper\IAAS\Facades\VM` facade (previously an orphaned, unbound facade with its own namespace-casing bug, also fixed in Phase 0).

---

## 2. Database Strategy Under the "Very Small Space" Constraint

Default position: **minimize new columns, spending the limited schema-change budget deliberately rather than avoiding it entirely.** The team confirmed one explicit, accepted exception: **agent/credential identity fields get real columns**, not JSON.

- **New columns, accepted exception — agent/driver credential identity** (not yet added, deferred to whenever the agent-transport work is picked up): `agent_uuid`, `agent_api_key`/secret (encrypted, following the existing `ssh_password`-style mutator pattern), `agent_latest_ping`, `available_operations` on `ComputeMembers`/`Repositories` (mirroring what `VirtualMachines` already has), plus `is_management_agent_available` newly on `StorageMembers`. Reused as the generic "how this driver authenticates" store for `*-rest` transport variants too, not just NATS agent credentials.
- **`Meta`/`MetaHelper`** stays the home for *non-secret* per-driver configuration (vCenter endpoint URL, Proxmox node name, libvirt connection URI, MoRefs).
- **Existing JSON blob columns** (`ComputeMembers.management_data`/`hypervisor_data`, `VirtualMachines.hypervisor_data`, `VirtualDiskImages.hypervisor_data`/`vbd_hypervisor_data`, `StorageVolumes.connection_parameters`, `StorageMembers.management_data`) are the de facto "driver payload" columns for non-credential runtime state.

**Phase 0 added zero new columns** — everything landed within the existing schema.

---

## 3. Transport Strategy

Support multiple transport implementations per backend, not one uniform choice — resolves cleanly given mixed host ownership (some environments self-owned, some customer/third-party-managed with API-credential-only access):

- **Agent-based** (`*-agent` driver variants) — reuses `v2-agent-architecture.md`'s NATS design. Confirmed for KVM: agent transport only, no SSH/`virsh`-wrapper fallback. Preferred (not required) for self-owned hosts of any backend.
- **Native-API-based** (`*-rest` driver variants) — for VMware/Proxmox, both of which already expose a typed, authenticated, network-reachable management surface. Required wherever credentials-only access applies.
- The driver interface makes this invisible to every Action/Job/Controller — callers only ever talk to `VirtualMachineManager`.

---

## 4. Per-Hypervisor Driver Notes (in rollout order)

**XenServer** (`Services/Hypervisors/XenServer/*` → `XenServer82SshDriver`, implemented in Phase 0) — not a rewrite. Wraps the existing `*XenService` classes behind the interface unchanged; call sites move from "Action calls XenService directly" to "Action calls `VirtualMachineManager`." A later `XenServer82AgentDriver` (per `v2-agent-architecture.md`) becomes a second driver class registered under a different `virtualization` suffix, not a rewrite of this one.

**XCP-ng** (not started) — near-free. XAPI-compatible open-source fork of XenServer: same `xe` CLI, same XML-RPC surface, same VHD/SR model. No second driver class needed — `config/virtualization.php` already maps `xcp-ng-8.2`/`xcp-ng-8.2-ssh` to `XenServer82SshDriver::class` with a `product` config key read only for display/detection (`detectVersion()` already handles this).

**Proxmox** (not started) — new `ProxmoxRestDriver`. PVE REST API, token auth. No agent required. Console via `vncproxy`/`spiceproxy` ticket+port. Events via task-status polling.

**VMware** (not started) — new `VmwareRestDriver`. vSphere REST API. No agent required. Console via WebMKS ticket. Events via `PropertyCollector`.

**KVM** (not started) — new `KvmAgentDriver`, confirmed agent-based via libvirt (likely `libvirt-php`). Console is the largest unknown: no built-in proxy/ticket layer, needs a websockify-or-equivalent noVNC bridge, ideally agent-mediated.

---

## 5. Event Pipeline Redesign

`src/Services/Events/VirtualMachineEvents.php` (`started`/`stopped`/`paused` → dispatches `HealthCheck`) is unchanged — the one clean, hypervisor-agnostic seam in the pipeline.

`NormalizedHypervisorEvent` (`src/ValueObjects/NormalizedHypervisorEvent.php`) is the shape every driver's `EventTranslatorCapableInterface::translate()` produces: `vmRef`, `type` (started/stopped/paused/modified/deleted), `changes` (normalized diffs, memory in MB not Xen's bytes), `occurredAt`, `raw`.

`XenServerEventTranslator` (`src/Services/Hypervisors/XenServer/Events/XenServerEventTranslator.php`) translates XenAPI's `vm`-class event vocabulary into this shape. `ComputeComputeMemberEventsJob::vmModOperation()` now resolves the originating `ComputeMembers`' driver via `ComputePools.virtualization` and reads through the translator, falling back to the original raw-field reads if the resolved driver doesn't implement the interface.

**Scope note:** only `vm`-class/`mod`-operation events (VM power-state changes) are normalized. XenAPI's other event classes (`message`/`sr`/`task`/`leo` — host/task-level telemetry: backup-progress polling, SR events) are left untouched in `ComputeComputeMemberEventsJob`, since they don't map onto a VM-centric normalized event and generalizing them is a separate, larger design question.

---

## 6. Phased Rollout Plan

**Sequencing principle:** the driver-abstraction layer (this document) and the SSH→NATS-agent transport migration (`v2-agent-architecture.md`) are independent efforts. Phase 0's XenServer driver wraps the *existing* SSH+`xe` code as-is; the transport swap becomes an internal detail of that driver later, invisible to every caller already migrated onto the driver interface.

**Phase 0 — Fix the scaffold, wire XenServer through it. (Partially landed.)**

Landed: scaffold bug fixes, all 6 new capability interfaces, `XenServer82SshDriver`, `config/virtualization.php` + service-provider wiring, `NormalizedHypervisorEvent` + `XenServerEventTranslator`, `ComputeComputeMemberEventsJob::vmModOperation()` rewired, VM power-state Actions (Start/Shutdown/ForceShutdown/Restart/ForceRestart/Pause/Unpause), Snapshot, Sync, ComputeMembers (Initiate/Scan/UpdateResources/UpdateStorageVolumes), VirtualNetworkCards Detach, VirtualDiskImages (Destroy/Detach/Resize/Create/Attach), `hypervisor_model` branching consolidated in `StorageVolumes/Scan.php` and `CollectGarbageNetworks.php`, `VirtualMachines/Backup.php` implemented from its previously-empty stub.

**Not yet landed, remaining Phase 0 surface:**
- VM Actions: `Commit.php` (670 lines, VM provisioning — the heaviest remaining file), `HealthCheck.php`, `ConvertToTemplate.php`, `Export.php`, `ExportAsMachineImage.php`, `Delete.php`, `MountCd.php`/`EjectCd.php`, `UpdateConfigurationIso.php`.
- Jobs: `VirtualMachines/Fix.php`, `GenerateCloudInitImage.php`.
- Console commands: `UpdateConfigurationIso.php` (the command, not the Action).
- Controllers: `ComputeMemberEventsController`, `VirtualMachinesConsoleController`.
- ComputeMembers: `CheckServices`, `MountVmRepo`, `UnmountVmRepo`, `MountIsoRepo` (no capability interface designed for repo mount/unmount or RRD/IPMI/events sidecar checks yet).
- `VirtualNetworkCards/Attach.php` (attaches an existing draft row in place — doesn't match `DiskCapableInterface`'s create-and-return-new-row shape, needs its own interface method or a different approach).
- `VirtualDiskImages/Copy.php` (no underlying disk-copy operation exists anywhere in this codebase to wrap — needs a new `xe vdi-copy` implementation in `VirtualDiskImageXenService` first).
- `StateChangeNotification.php` — found with a dead early-return silently disabling an otherwise-complete, actively-registered email notification listener (`BindIAASEventHelper` wires it to real VM events). Left untouched deliberately: reactivating it starts sending live customer emails, a product decision requiring explicit sign-off, not a bug fix to make unilaterally.

**Migration-related call sites are explicitly excluded from Phase 0 entirely** (`MigrateVirtualMachine.php`, `MigrateLocalVirtualMachine.php`, `VirtualMachineMigrationsController`, `HypervisorsV2/XenServer_8_2/{MigrationService,LocalDiskMigrationService}.php`) per the confirmed migration deferral below.

**Phase 1 — XCP-ng.** Near config-only addition per §4.

**Phase 2 — Proxmox.** Simplest of the three remaining new-transport backends.

**Phase 3 — VMware.** Larger surface than Proxmox but still no agent-install requirement.

**Phase 4 — KVM.** Last — needs both net-new agent-transport work and a from-scratch console/VNC proxy layer.

**Not blocking the above, runs in parallel or later:**
- The `v2-agent-architecture.md` transport migration for XenServer/XCP-ng (and KVM's agent, when reached) is its own workstream.
- **Migration is explicitly deferred as its own dedicated initiative** — cross-host VM migration is a large, high-value feature (4000+ lines of existing XenServer/VHD migration logic) deserving focused attention on its own timeline.
- `HypervisorEvacuationXenService`'s hand-rolled per-VM evacuation stays XenServer/XCP-ng-specific and — per the confirmed mixed real-pool/virtual-pool finding — **stays permanently necessary for virtual-pool deployments**, not just until native pool membership arrives.

---

## 7. Risks / Open Questions

### Resolved (confirmed by the team)

1. **Native pool membership.** Confirmed mixed in production, not converging on one model. `HostSyncInterface` detects and records which mode a host is in; `HypervisorEvacuationXenService` stays permanently required for virtual-pool deployments.
2. **Migration scope.** Confirmed explicitly deferred as its own later initiative.
3. **KVM transport.** Confirmed agent-based, not SSH/`virsh`-wrapper.
4. **Agent/credential column budget.** Confirmed acceptable to add real columns for agent identity and driver credentials specifically, rather than an all-JSON approach.

### Still open

5. **Console/VNC scope for day one per backend** — given KVM's console mechanism is a from-scratch build, confirm it's acceptable to ship VM lifecycle + disk + network for a backend before its console implementation lands.
6. **Secret storage / encryption convention** — confirm the `ssh_password`-style encrypted-mutator pattern is the intended approach for `agent_api_key` too.
7. **`libvirt-php` as a new PHP extension dependency** (KVM) — confirm operationally acceptable, comparable to the existing `ext-ssh2` dependency.
8. **Image compatibility metadata across backends** — `RepositoryImages.supported_virtualizations` reuse is confirmed for gating which images work on which backend, but per-backend disk-format/driver requirements (qcow2 vs. VHD vs. VMDK) aren't addressed in depth here.
9. **`StateChangeNotification.php`** (new, found during Phase 0) — should this notification listener be reactivated? It's fully implemented and actively registered, just disabled by a dead early-return. Needs explicit product sign-off, not a unilateral code fix.
10. **`VirtualMachines::suspend()`/`listAll()`** (new, found during Phase 0) — both are mandatory `VirtualMachineAdapterInterface` methods with no underlying XenService operation to wrap; `XenServer82SshDriver` currently throws for both. Worth deciding whether these should move to a capability interface instead of staying mandatory.

---

## Critical Files Reference

- `src/Contracts/*.php` — driver interfaces (`VirtualMachineAdapterInterface` + 8 capability interfaces)
- `src/ValueObjects/{ConsoleSession,NormalizedHypervisorEvent}.php` — driver-agnostic value objects
- `src/Services/HypervisorsV2/VirtualMachineManager.php` — the registry/dispatch point every caller goes through
- `src/Services/Hypervisors/XenServer/XenServer82SshDriver.php` — the XenServer/XCP-ng driver implementation
- `src/Services/Hypervisors/XenServer/Events/XenServerEventTranslator.php` — XenAPI event normalization
- `config/virtualization.php` — platform → driver class registration
- `src/Jobs/ComputeComputeMemberEventsJob.php` + `src/Services/Events/VirtualMachineEvents.php` — event normalization boundary
- `docs/v2-agent-architecture.md` — authoritative prior art for the transport-layer workstream this document deliberately decouples from
- `src/Services/HypervisorsV2/XenServer_8_2/{MigrationService,LocalDiskMigrationService}.php` + `src/Services/HypervisorsV2/MigrationInterface.php` — untouched per the confirmed migration deferral
- `src/Services/Hypervisors/XenServer/HypervisorEvacuationXenService.php` — confirmed permanent for virtual-pool deployments, not a stepping-stone

## How to Validate

1. The XenServer driver wrapping existing `*XenService` calls must be behavior-identical to before Phase 0 — verify via manual QA against a real XenServer host (start/stop/snapshot/disk operations, console access) before trusting this in production.
2. `grep -rn hypervisor_model src/Actions src/Jobs src/Services` should show only display-only reads and the three explicitly-deferred image-compatibility files, no other branching.
3. `Backup.php` is newly-implemented, unverified code (the `handle()` method was previously empty) — needs real-host QA on a test VM before being trusted for production backups.
4. Confirm the `xenserver-8.2` bare-string alias in `config/virtualization.php` actually resolves for real `ComputePools` rows (`php artisan tinker` → resolve a driver for an existing pool) before deploying.
