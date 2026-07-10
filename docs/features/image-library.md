# Image Library

The image library is where operating system templates, ISO installers, and Docker images live. It's what powers "launch a VM from Ubuntu 22.04" or "boot from this ISO" — every VM you create starts from an image stored here.

## Key Capabilities

- Repositories hold collections of VM images, ISOs, and Docker images
- Each image records its operating system, distribution, version, and recommended CPU/RAM
- Images can be marked public (available to everyone) or private (account-only)
- Cloud-init-ready images support automatic first-boot configuration
- Repositories can be synchronized to discover new ISOs or VM images placed on disk
- Any VM can be converted into a new image for reuse, including ones created from your own customized servers

## Repositories

A repository is a managed image store tied to a cloud node. A single repository can serve as a VM image library, an ISO repository, a Docker registry, or a backup destination — these roles aren't mutually exclusive. Repositories support SSH-based or agent-based synchronization, so when new images are added to the underlying storage, the platform picks them up automatically.

## Images

Each image in a repository carries metadata that helps you pick the right one: operating system, distribution, version, CPU architecture, recommended CPU/RAM, and whether it supports cloud-init for automatic configuration at first boot (hostname, SSH keys, users, etc.). Images can come from three sources:

- **Official OS images** — standard distributions like Ubuntu, Debian, CentOS, Windows
- **ISO installers** — for manual or custom OS installation
- **Your own templates** — any VM can be converted into a reusable image

## Synchronization

When new files are added directly to a repository's storage (new ISOs, new VM images), running a sync operation scans the repository and registers them as usable images, including computing checksums and sizes.

## API Examples

**List available images in a repository**

```
GET /iaas/repository-images?filter[iaas_repository_id]=<repository_id>
```

**Create a repository image record**

```
POST /iaas/repository-images
```
```json
{
  "name": "Ubuntu 22.04 LTS",
  "iaas_repository_id": "550e8400-...-uuid",
  "is_virtual_machine_image": true,
  "os": "Linux",
  "distro": "Ubuntu",
  "version": "22.04",
  "cpu": 2,
  "ram": 2,
  "is_cloudinit_image": true
}
```

**Trigger a repository sync**

```
POST /iaas/repositories/{id}/do/synchronize-isos
POST /iaas/repositories/{id}/do/synchronize-machine-images
```

## Related Features

- [Virtual Machines](virtual-machines.md) — every VM is created from an image in the library
- [Storage](storage.md) — images are backed by storage volumes/repositories
- [Automation](automation.md) — cloud-init images combine with environment variables for first-boot setup
