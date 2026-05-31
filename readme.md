# NextDeveloper IAAS Module

This module started as the PlusClouds IaaS service module and was later moved under the NextDeveloper brand and open-sourced. Anyone who wants to provide an IaaS service to their customers can use this module freely.

---

## Features

### Virtual Machine Management

- Full VM lifecycle: create, start, stop, pause, unpause, restart, force-restart, force-shutdown, destroy
- VM cloning and snapshot management (take snapshot, convert snapshot to VM)
- VM parameter sync and name fixing with optional ISO 27001 compliance mode
- Virtual disk image (VDI) sync and device management
- Virtual network interface (VIF) management: create, destroy, sync

### Configuration ISO (cloud-init)

- Automatic generation and upload of per-VM configuration ISOs to the central NFS ISO repository
- **Linux VMs**: includes `user-data`, `meta-data`, `pc-meta-data.json`, Ansible playbooks, optional post-boot script, and the PlusClouds agent binary + service unit
- **Windows VMs**: includes `pc-meta-data.json` and PowerShell configuration scripts (hostname, password, SSH keys, env vars, startup task)
- Artisan command to manually trigger ISO regeneration for any VM:

  ```bash
  php artisan leo:update-configuration-iso {uuid}
  ```

### PlusClouds VM Agent

- Per-VM agent binary (`plusclouds.linux` / `plusclouds.windows`) bundled into the configuration ISO
- Agent identified by a unique `agent_api_key` generated at VM creation time
- NATS-based agent listener (`leo:listen-vm-agent-events`) that receives telemetry events and evaluates health metrics
- Health checks include: per-core CPU usage, disk I/O, memory, and custom system comments
- Agent command dispatch via `VmAgentCommandService` and `VirtualMachineAgentCommandsController`
- Systemd service unit (`plusclouds-agent.service`) and Ansible deploy playbook (`deploy-service.yml`) for automated agent installation on VMs

### VM Configuration Playbooks (Ansible / cloud-init)

| Playbook | Purpose |
| --- | --- |
| `apply-configuration.yml` | Applies full VM configuration on first boot |
| `apply-locale.yml` | Sets system locale |
| `change-hostname.yml` | Updates the VM hostname |
| `change-password.yml` | Rotates the root/admin password |
| `apply-env-vars.yml` | Injects environment variables |
| `apply-ssh-keys.yml` | Configures authorised SSH keys |
| `disk-resize-debian12.yml` | Resizes root disk on Debian 12 |
| `disk-resize-ubuntu22.yml` | Resizes root disk on Ubuntu 22 |
| `disk-resize-ubuntu24.yml` | Resizes root disk on Ubuntu 24 |
| `run-post-boot-script.yml` | Executes user-provided post-boot script |
| `run-startup-script.yml` | Executes startup script on every boot |
| `deploy-service.yml` | Deploys and enables the PlusClouds agent service |

### VM Export & Backup

- Export VM to a repository (synchronous and background/nohup modes)
- Export to default backup repository
- Backup completion webhook via `finalize-backup` endpoint
- Running backup detection to prevent concurrent exports

### Cloud Node & Compute Pool Management

- `leo:sync-cloud-node` — syncs all compute pools under a given cloud node
- Storage volume sync and compute member service health checks
- CD-ROM mount/unmount operations

### Authorization & Roles

- `CloudSalesPerson` role with scoped IaaS management permissions
- Account-based IAM permission enforcement via `BackfillIaasAccount` action

### Artisan Commands

| Command | Description |
| --- | --- |
| `leo:update-configuration-iso {uuid}` | Regenerate and upload the config ISO for a VM |
| `leo:sync-virtual-machine` | Sync one or all VMs with the hypervisor |
| `leo:sync-cloud-node` | Sync all compute pools under a cloud node |
| `leo:vm-health-check` | Run health checks on all active VMs |
| `leo:listen-vm-agent-events` | Start the NATS VM agent event listener |
| `leo:compute-member-service-check` | Check compute member service status |
| `leo:sync-storage-volume` | Sync storage volumes |
| `leo:migrate-virtual-machine` | Migrate a VM to another compute member |
| `leo:migrate-local-virtual-machine` | Migrate a VM locally via dd/rsync |
| `leo:transfer-virtual-machine` | Transfer a VM between nodes |
| `leo:remove-lost-servers` | Clean up VMs with no hypervisor record |
| `leo:remove-draft-servers` | Remove VMs stuck in draft state |

---

## Support

For support we have man/hour and yearly support packages including:

- Machine images
- Metadata service for fast VM provisioning
- Updates and constant security checks
- Business model consultancy and design

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
