# Virtual Machines

Virtual machines are the core compute resource on the platform. You can launch a VM from a ready-made operating system image in seconds, scale its CPU and RAM as your workload grows, take point-in-time snapshots before risky changes, and turn any VM into a reusable template for future deployments — all without touching the underlying hardware.

## Key Capabilities

- Launch Linux or Windows virtual machines from a library of operating system images
- Start, stop, restart, or force-restart a VM on demand
- Pause a running VM and resume it later without losing in-memory state
- Resize CPU and RAM as workloads change
- Take instant snapshots and restore from them
- Convert any VM into a template to spin up identical copies later
- Mount or eject virtual CD-ROM/ISO media
- Run remote commands through the in-VM agent without needing SSH access configured up front
- Automatic, schedulable backups with a configurable daily backup window

## Lifecycle and Power Operations

Every VM moves through a simple lifecycle: it's created in a draft state while it's being provisioned, then becomes active once it's deployed to a compute host. From there you can start, stop, restart, pause, and unpause it freely. Force-shutdown and force-restart are available for cases where a VM has stopped responding to a graceful request. The platform also tracks which operations are currently valid for a VM (for example, you can't "start" a VM that's already running) so the available actions always reflect real state.

## Snapshots and Templates

A snapshot captures a VM at a specific point in time. Snapshots are useful before risky upgrades or configuration changes — if something goes wrong, you roll back instead of rebuilding. Converting a VM into a template takes this further: a template becomes a reusable starting point that you (or your account) can launch new VMs from, exactly as if it were one of the platform's built-in operating system images.

## Resource Sizing

CPU core count and RAM are adjustable per VM. Resizing doesn't require migrating to different hardware — the platform updates the VM's allocation directly on its current host where supported.

## Console and Remote Access

Each VM exposes console connection details so you can access it directly from the dashboard, even before networking or SSH is fully configured. For Windows VMs, WinRM connectivity can be enabled for remote management. An in-VM agent can also be deployed to report health and accept commands without opening a separate management channel.

## Automatic Backups

Every VM can have an automatic backup interval (for example, daily) and a preferred backup time window configured directly on the VM. Backups feed into the platform's broader [Backup & Disaster Recovery](backup-and-disaster-recovery.md) system, including retention policies and restore.

## API Examples

**Create a virtual machine**

```
POST /iaas/virtual-machines
```
```json
{
  "name": "web-server-01",
  "cpu": 2,
  "ram": 4096,
  "iaas_compute_pool_id": "5f0c2b3a-...-uuid",
  "iaas_repository_image_id": "9a31d4e0-...-uuid",
  "auto_backup_interval": "daily",
  "auto_backup_time": "02:00"
}
```
```json
{
  "id": "8d2e0a1b-...-uuid",
  "name": "web-server-01",
  "cpu": 2,
  "ram": 4096,
  "status": "halted",
  "is_draft": true,
  "created_at": "2026-06-27T12:00:00Z"
}
```

**Start a virtual machine**

```
POST /iaas/virtual-machines/{id}/do/start
```

**List virtual machines**

```
GET /iaas/virtual-machines
```

## Related Features

- [Image Library](image-library.md) — choose the operating system image or template a VM is created from
- [Service Roles](service-roles.md) — pre-install services like Docker or PostgreSQL on a VM at boot
- [Networking](networking.md) — attach network cards and IP addresses to a VM
- [Storage](storage.md) — attach, resize, or expand disks on a VM
- [Backup & Disaster Recovery](backup-and-disaster-recovery.md) — scheduled backups and restore for VMs
- [Monitoring & Alerts](monitoring-and-alerts.md) — health checks and performance metrics for running VMs
