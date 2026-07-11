# IaaS Platform Features

This is the feature documentation for the platform's Infrastructure-as-a-Service (IaaS) capabilities — virtual machines, networking, storage, and everything else needed to run workloads in the cloud. Each page below explains a feature area in plain language and includes brief API examples for the key operations in that area.

All API paths are relative to the platform's API base URL and prefixed with `/iaas`. Resources are addressed by UUID, and every resource type follows the same basic operations: list (`GET`), create (`POST`), show/update/delete (`GET` / `PATCH` / `DELETE` on `/{id}`), and custom actions (`POST /{id}/do/{action}`).

## Feature Areas

| Feature | What it covers |
| --- | --- |
| [Virtual Machines](virtual-machines.md) | Launching, sizing, starting/stopping, snapshotting, and templating VMs |
| [Networking](networking.md) | Networks, network cards, IP addresses, gateways, and DHCP |
| [Storage](storage.md) | Storage pools/volumes and virtual disk attach, resize, and clone |
| [Image Library](image-library.md) | OS images, ISOs, and Docker images that VMs are created from |
| [Automation](automation.md) | Ansible playbooks, environment variable groups, and SSH key management |
| [Service Roles](service-roles.md) | Pre-installing services like Docker or PostgreSQL on a VM at boot |
| [Monitoring & Alerts](monitoring-and-alerts.md) | Health checks, CPU/network/storage metrics, and active alarms |
| [Backup & Disaster Recovery](backup-and-disaster-recovery.md) | Scheduled backups, retention policies, recovery targets, and restore |
| [Datacenters & Infrastructure](datacenters-and-infrastructure.md) | Datacenters, cloud nodes, compute pools, and compute hosts |
| [Resource Management & Quotas](resource-management-and-quotas.md) | Account resource limits, compute pool capacity, and licensing |

## How These Pages Are Organized

Each page follows the same structure: a short overview, a list of key capabilities, a deeper explanation of each sub-feature, brief API examples for the most common operations, and links to related feature areas.
