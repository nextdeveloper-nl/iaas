# NextDeveloper IAAS Module

> **Turn any infrastructure into a production-grade IaaS platform in days, not months.**

The NextDeveloper IAAS module started as the PlusClouds IaaS service layer and has been battle-tested at scale across multiple cloud regions. It is now fully open-sourced under the NextDeveloper brand so that any business can deliver Infrastructure-as-a-Service to their own customers — without rebuilding the foundation from scratch.

From provisioning a virtual machine to orchestrating cross-node live migrations, managing backup schedules, enforcing resource quotas, and streaming real-time telemetry from in-VM agents, this module handles the complete IaaS lifecycle inside a Laravel application.

---

## What You Get

- **Full VM Lifecycle Management** — provision, start, stop, pause, restart, snapshot, clone, migrate, export, and destroy
- **Multi-hypervisor architecture** — production-ready XenServer / Citrix Hypervisor 8.2 driver, pluggable adapter interface for adding others
- **Cloud-init & Windows Unattend** — automatic per-VM configuration ISO generation for Linux (Ansible) and Windows (PowerShell)
- **In-VM Agent** — NATS-based agent binary that streams telemetry and accepts remote commands from the platform
- **Ansible Automation** — built-in playbooks for hostname, password, SSH keys, disk resize, env vars, locale, and agent deploy
- **Backup & Disaster Recovery** — scheduled backups, multi-level replication, retention policies, heatmaps
- **Advanced Networking** — VLAN management, IP allocation, DHCP (ISC & KEA), gateway management, ARP-based IP discovery
- **Storage Management** — storage pools, members, volumes, VDI attach/detach/resize/copy, per-volume statistics
- **Compute & Cloud Node Management** — datacenter hierarchy, cloud nodes, compute pools, compute members with full resource tracking
- **Monitoring & Analytics** — per-VM CPU/disk/network metrics, hourly and daily stats, KPI performance, alarm management
- **Docker Registry Support** — integrated private registry synchronisation and management
- **Role-Based Access Control** — six built-in roles from CloudSpectator to CloudNodeAdmin, with pluggable custom authorisation
- **RESTful API** — 100+ resource controllers with consistent filtering, sorting, and pagination out of the box

---

## Hypervisor Support

### XenServer / Citrix Hypervisor 8.2

The primary production driver. Every XenServer operation is exposed through a clean service layer so you can call it programmatically or fire it from an Action, a Job, or an Artisan command.

#### VM Operations

| Operation | Description |
| --- | --- |
| Start / Stop / Pause / Unpause | Full power-state management |
| Restart / Force Restart | Graceful and forced reboot |
| Shutdown / Force Shutdown | Graceful and hard power off |
| Snapshot | Point-in-time VM snapshot |
| Convert Snapshot → VM | Promote any snapshot to a runnable VM |
| Clone | Full VM clone with new UUID |
| Export / Import | Export to `.pvm` or a repository; import machine images |
| Live Migration | Cross-node migration via `xe vm-migrate` |
| Local Migration | Same-host storage migration via `dd` / `rsync` over SSH |
| Node Evacuation | Drain all VMs off a compute member |

#### Storage Operations

| Operation | Description |
| --- | --- |
| VDI Create / Attach / Detach / Resize / Copy | Full virtual disk lifecycle |
| SR Scan | Force-refresh the XenServer storage repository |
| VBD Management | Manage virtual block device connections |
| CD-ROM Mount / Unmount | Mount and eject ISO images at runtime |

#### Networking

| Operation | Description |
| --- | --- |
| VIF Create / Destroy | Virtual network interface management |
| VIF Sync | Reconcile live hypervisor state with the database |
| Bridge / VLAN management | Managed through `NetworkMemberXenService` |

#### Metrics Collection

- RRD data pulled via `rrd.py` from the XenServer host
- IPMI integration via `ipmi.py` for hardware-level telemetry
- XenServer event stream subscription via `events.py`

### Pluggable Adapter Architecture

Additional hypervisors can be added by implementing three interfaces:

- `VirtualMachineAdapterInterface` — core VM operations
- `CloneCapableInterface` — VM cloning
- `SnapshotCapableInterface` — snapshot operations
- `ResizeCapableInterface` — CPU / RAM / disk resize

The `VirtualMachineManager` class resolves the correct adapter at runtime based on the compute member type.

---

## Virtual Machine Features

### Provisioning

- Create VMs from marketplace images or repository machine images
- Automatic `agent_api_key` generation per VM for secure agent authentication
- Cloud-init ISO auto-generated and uploaded to the NFS ISO library on every configuration change
- Metadata API endpoint with full VM context for in-guest bootstrap

### Configuration ISO (cloud-init / Windows Unattend)

Every VM has a dedicated configuration ISO that is regenerated and re-uploaded automatically whenever VM configuration changes. The ISO includes:

#### Linux VMs

- `user-data` — cloud-init user data
- `meta-data` — instance ID and hostname
- `pc-meta-data.json` — PlusClouds-specific metadata
- All Ansible playbooks (see Automation section)
- PlusClouds agent binary + `plusclouds-agent.service` systemd unit
- Optional `post-boot-script.sh` for user-defined first-boot commands

#### Windows VMs

- `pc-meta-data.json` — PlusClouds-specific metadata
- `apply-configuration.ps1` — master configuration script
- `change-hostname.ps1`, `change-password.ps1`
- `apply-env-vars.ps1`, `apply-ssh-keys.ps1`
- `register-startup-task.ps1` — registers the agent as a Windows startup task

Manually trigger ISO regeneration for any VM:

```bash
php artisan leo:update-configuration-iso {uuid}
```

### VM Actions (fully dispatchable jobs)

| Action | Description |
| --- | --- |
| Start | Boot the VM |
| Shutdown / ForceShutdown | Graceful or hard power off |
| Restart / ForceRestart | Graceful or forced reboot |
| Pause / Unpause | Suspend and resume execution |
| Snapshot | Take a point-in-time snapshot |
| Backup | Run a full VM backup |
| Delete | Destroy the VM and release resources |
| Sync | Reconcile VM state with the hypervisor |
| HealthCheck | Evaluate VM health and update status |
| Commit | Apply pending configuration changes |
| ConvertToTemplate | Promote VM to a reusable template |
| Lock / Unlock | Prevent or allow modifications |
| MountCd / EjectCd | Attach or remove a CD-ROM image |
| Export | Export VM to a repository |
| StateChangeNotification | Fire notifications on power state changes |

### Live and Local Migration

- **Cross-node migration** (`leo:migrate-vm`) — interactive propose/approve/execute workflow, supports single-disk and multi-VHD VMs with per-disk storage mapping
- **Local migration** (`leo:migrate-local-vm`) — same-host migration using `dd` or `rsync` over SSH when source and target storage are on the same storage member
- **Node evacuation** — drain all running VMs off a compute member safely

---

## PlusClouds VM Agent

Every VM provisioned by the platform receives a lightweight agent that connects back over NATS using mutual TLS.

### How It Works

1. The agent binary (`plusclouds.linux` or `plusclouds.windows`) is embedded in the configuration ISO
2. A unique `agent_api_key` is generated per VM at creation time and injected into the agent config via `agent.yaml`
3. The agent registers itself with the platform on first boot and starts streaming telemetry
4. The platform listens on `iaas:vm-agent-listen` and evaluates incoming events

### Telemetry & Health Evaluation

The NATS listener (`ListenVmAgentEvents`) receives and processes:

- Per-core CPU usage
- Disk I/O metrics
- Memory utilisation
- Custom system comments from the VM
- Hardware health signals

Health problems are automatically surfaced as VM state comments and can trigger alerts.

### Remote Command Dispatch

Commands can be dispatched to any running VM via `VmAgentCommandService` through the `VirtualMachineAgentCommandsController` REST endpoint. This enables zero-SSH remote operations on VMs that have the agent running.

### Agent Installation (systemd)

```bash
sudo nano /etc/systemd/system/plusclouds.service
# Paste the contents of plusclouds-agent.service

sudo systemctl enable plusclouds.service
sudo systemctl daemon-reload
sudo systemctl start plusclouds.service
```

Or use the automated Ansible playbook:

```bash
# Included in the VM configuration ISO as deploy-service.yml
```

---

## Ansible Automation

Built-in playbooks are bundled into every Linux VM's configuration ISO and executed by cloud-init on boot.

| Playbook | Purpose |
| --- | --- |
| `apply-configuration.yml` | Master playbook — applies all configuration on first boot |
| `change-hostname.yml` | Sets the VM hostname |
| `change-password.yml` | Rotates the root / admin password |
| `apply-ssh-keys.yml` | Injects authorised SSH public keys |
| `apply-env-vars.yml` | Writes environment variables |
| `apply-locale.yml` | Configures system locale |
| `disk-resize-debian12.yml` | Resizes root disk on Debian 12 |
| `disk-resize-ubuntu22.yml` | Resizes root disk on Ubuntu 22 |
| `disk-resize-ubuntu24.yml` | Resizes root disk on Ubuntu 24 |
| `run-post-boot-script.yml` | Executes the user-provided post-boot script |
| `run-startup-script.yml` | Runs the startup script on every boot |
| `deploy-service.yml` | Installs and enables the PlusClouds agent service |

---

## Backup & Disaster Recovery

### Backup Engine

- **Scheduled backups** with configurable retention policies
- **Multi-level replication** across backup repositories via `BackupJobReplications`
- **Background export** using `nohup` + `xe vm-export` so exports survive SSH disconnects
- **Backup completion webhooks** — the hypervisor calls back to `/public/iaas/finalize-backup/{uuid}` on completion
- **Concurrent export protection** — running backup detection prevents double-exports

### Backup Actions

| Action | Description |
| --- | --- |
| RunBackupJob | Execute a scheduled backup job |
| InitiateMultilevelBackupJob | Start a multi-replica backup across repositories |
| FinishBackupJob | Finalise and verify a completed backup |
| DeleteOldBackups | Enforce retention policies and purge expired backups |
| Delete (VirtualMachineBackups) | Delete an individual backup |

### Reporting

- Per-VM backup heatmaps (by cloud node and account)
- Backup job statistics and performance history
- `VmBackupJobsPerspective` for operational dashboards

---

## Networking

### Network Model

The network stack is modelled as a hierarchy: **Cloud Node → Network Pool → Network → Network Member → VIF**

- VLAN-based network isolation
- ARP-based IP discovery (`UpdateIpsWithArp`) for automatic IP reconciliation
- IP address lifecycle management: attach / detach / history
- Gateway management: create, commit, delete

### DHCP

Two DHCP server implementations are supported:

| Implementation | Service Class |
| --- | --- |
| ISC DHCP (dhcpd) | `IscDhcpServices` |
| ISC Kea DHCP | `IscKeaServices` |

`DhcpServers` model tracks configuration state; `UpdateConfiguration` action pushes changes to the active server.

### Network Statistics

Per-network and per-pool statistics are collected in hourly and daily intervals, accessible via dedicated perspective models and controllers.

---

## Compute Management

### Compute Hierarchy

Datacenter → Cloud Node → Compute Pool → Compute Member → Virtual Machine

### Compute Member Features

- Resource tracking: CPU, RAM, storage utilisation
- Service health check with optional re-deployment (`leo:cm-service-check`)
- ISO and VM repository mount/unmount operations
- Event aggregation and task tracking
- Per-member statistics: `ComputeMemberMetrics`, `ComputeMemberStats`
- Network interface scanning and synchronisation

### Compute Pool Features

- Pool-level VM scan and import
- Resource aggregation across all members
- ISO 27001 compliance mode (anonymise VM names in hypervisor)

### Datacenter Features

- Initiation workflow for new datacenters
- Resource scan and update
- Hierarchical resource reporting

---

## Storage Management

### Storage Hierarchy

Storage Pool → Storage Member → Storage Volume → Virtual Disk Image

### Storage Volume Features

- Scan and sync with hypervisor SR state
- Volume statistics: utilisation, IOPS, throughput
- Cross-member volume visibility

### Virtual Disk Image Features

| Action | Description |
| --- | --- |
| Create | Provision a new VDI on a storage volume |
| Attach / Detach | Connect or disconnect a disk from a VM |
| Resize | Expand a virtual disk online |
| Copy | Duplicate a VDI to another storage |
| Sync | Reconcile VDI state with the hypervisor |

---

## Repository & Machine Image Management

### Repositories

- **ISO repositories** — NFS-mounted ISO library for cloud-init and OS images
- **VM repositories** — store exported VM images (`.pvm` format)
- **Docker registries** — integrated private container registry support

### Machine Image Actions

| Action | Description |
| --- | --- |
| SynchronizeIsos | Scan and index all ISOs in the repository |
| SynchronizeMachineImages | Scan and index all VM images |
| SynchronizeDockerImages | Sync Docker image tags from a private registry |
| CloneImage | Clone a repository image to another repository |
| Initiate | Bootstrap a new repository with required directory structure |

### Artisan Sync Commands

```bash
php artisan leo:sync-repository-machine-images
php artisan leo:sync-marketplace-products
```

---

## Monitoring & Analytics

### Per-VM Metrics

- CPU: per-core utilisation, hourly aggregates, alerts on threshold breach
- Disk: per-disk I/O metrics via VDI stats
- Network: per-VIF traffic statistics
- Health: composite health score updated on every `HealthCheck` action run

### Platform-Wide Analytics

| Model / Service | Granularity |
| --- | --- |
| CloudNodeDailyStats / HourlyStats | Per cloud node, hourly and daily |
| ComputeMemberStats / Metrics | Per compute member |
| StorageMemberStats / VolumeStats | Per storage member and volume |
| NetworkStats / NetworkPoolStats | Per network and pool |
| AccountCurrentStats / HourlyStats / HourlyPerformance | Per tenant account |
| VmDailyStats / VmHourlyStats | Per virtual machine |
| KpiPerformance | Platform-level KPIs |

### Alarm Management

- `ActiveAlarmsPerspective` — live view of all current alarms
- `VirtualMachineCpuAlerts` — configurable CPU threshold alerting
- `HealthChecksPerformance` — historical health check scoring

---

## Ansible Server Integration

For VMs and infrastructure nodes that require configuration management beyond cloud-init:

- **AnsibleServers** — register Ansible control nodes
- **AnsiblePlaybooks** — store and version playbooks
- **AnsibleRoles** — manage reusable Ansible roles
- **AnsiblePlaybookExecutions** — track every playbook run with full output
- **AnsibleSystemPlaybooks / Plays** — platform-managed system playbooks for internal operations

---

## Authorization & Roles

| Role | Level | Description |
| --- | --- | --- |
| `CloudSpectator` | Read-only | View resources without modification rights |
| `CloudSalesPerson` | Limited | Resource visibility for sales operations |
| `IaasSuccessManager` | Elevated | Customer success and account management |
| `DatacenterAdmin` | High | Datacenter administration |
| `CloudResourceOwner` | Full | Full resource creation and management (NIN-validated for compliance) |
| `CloudNodeAdmin` | Highest | Complete infrastructure access |

Turkish NIN (National ID Number) validation is built in as an authorisation rule for `CloudResourceOwner` to satisfy local compliance requirements.

---

## Artisan Commands

| Command | Description |
| --- | --- |
| `leo:update-configuration-iso {uuid}` | Regenerate and upload the config ISO for a VM |
| `leo:sync-virtual-machine [--uuid=] [--fg=]` | Sync one or all VMs with the hypervisor |
| `leo:vm-health-check` | Run health checks on all active VMs |
| `iaas:vm-agent-listen` | Start the NATS VM agent telemetry listener |
| `leo:sync-cloud-node {slug}` | Sync all compute pools under a cloud node |
| `leo:cm-service-check` | Check and optionally redeploy compute member services |
| `leo:sync-storage-volume` | Sync storage volumes with the hypervisor |
| `leo:sync-repository-machine-images` | Scan and index machine images in all repositories |
| `leo:sync-marketplace-products` | Sync marketplace product catalogue |
| `leo:migrate-vm` | Interactive cross-node VM migration (propose/approve/execute) |
| `leo:migrate-local-vm` | Same-host storage migration via dd/rsync |
| `leo:transfer-vm` | Transfer VM ownership between accounts |
| `leo:vm-remove-drafts` | Remove VMs stuck in draft state |
| `leo:vm-remove-lost` | Remove orphaned VMs with no hypervisor record |

---

## Database Models

100+ Eloquent models covering the complete IaaS domain. Every model ships with:

- UUID primary key
- Soft deletes
- Automatic cache invalidation
- Observer-driven event publishing
- Advanced query filters (sortable, searchable, paginated)
- Perspective models for dashboard and reporting queries

**Core models include:** `CloudNodes`, `Datacenters`, `ComputePools`, `ComputeMembers`, `StoragePools`, `StorageMembers`, `StorageVolumes`, `Networks`, `NetworkPools`, `NetworkMembers`, `VirtualMachines`, `VirtualDiskImages`, `VirtualNetworkCards`, `VirtualMachineBackups`, `BackupJobs`, `BackupSchedules`, `Repositories`, `RepositoryImages`, `IpAddresses`, `Gateways`, `DhcpServers`, `AnsibleServers`, `AnsiblePlaybooks`, and many more.

---

## REST API

Every resource is exposed via a consistent RESTful API:

- **100+ controllers** covering all resources
- Standard CRUD operations (`index`, `store`, `show`, `update`, `destroy`)
- Query filter support on all list endpoints
- Perspective endpoints for aggregated and reporting views
- Action endpoints for triggering VM and infrastructure operations

---

## Support

For support we have man/hour and yearly support packages including:

- Machine images ready for provisioning
- Metadata service for fast VM bootstrap
- Continuous updates and security patches
- Business model consultancy and IaaS architecture design

Support is provided under PlusClouds BV, under the brand called LEO. Please reach out to:
[sales@plusclouds.com](mailto:sales@plusclouds.com)

---

## Our Libraries

This library is part of the **NextDeveloper / PlusClouds open-source ecosystem**. Browse all available libraries and find the right building blocks for your next project:

[https://plusclouds.com/us/solutions/libraries](https://plusclouds.com/us/solutions/libraries)

---

## Join the Community

We believe great software is built together. The PlusClouds developer community is a place where engineers share ideas, ask questions, showcase what they have built, and help shape the direction of these libraries. Whether you are integrating a single package or building an entire platform on top of our stack, you are very welcome here.

Come and join us — we would love to see what you build:

[https://plusclouds.com/us/community](https://plusclouds.com/us/community)
