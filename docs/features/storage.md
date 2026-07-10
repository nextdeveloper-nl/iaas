# Storage

Every virtual machine's disks live on the platform's storage layer, which pools physical storage hardware into volumes that VMs draw from. You can attach, detach, resize, and clone disks independently of the VM they belong to, and the platform tracks capacity and health for you.

## Key Capabilities

- Storage pools group together storage hardware (HDD, SSD, or other backing types) for allocation
- Virtual disks can be attached to or detached from a VM
- Disks can be resized (grown or shrunk) without rebuilding the VM
- Disks can be cloned/copied to create an independent duplicate
- Storage volumes are scanned for capacity and health automatically
- Storage can be dedicated to VM disks, used as an image repository, or used for virtual CD-ROM media

## Storage Volumes and Pools

A storage volume represents a chunk of physical storage capacity — tracked with total, used, and free space — that lives inside a storage pool. Pools let the platform spread virtual disks across available hardware and report whether a volume is healthy and reachable. A volume can serve different purposes: general VM disk storage, a backing store for the image repository, or CD-ROM/ISO media.

## Virtual Disks

A virtual disk (attached to a VM) is what the operating system actually sees as a hard drive. Disks can be:

- **Attached** to a VM as an additional drive
- **Detached** from a VM, leaving the data intact for later reattachment
- **Resized**, growing or shrinking the available space
- **Copied**, producing a new, independent disk with the same contents
- **Destroyed**, permanently removing the disk

Each disk tracks both its allocated size and its actual physical usage, so you can see how much space is really being consumed versus reserved.

## API Examples

**Create a storage volume**

```
POST /iaas/storage-volumes
```
```json
{
  "name": "storage-vol-01",
  "disk_physical_type": "SSD",
  "total_hdd": 500,
  "iaas_storage_pool_id": "1b6f0a2c-...-uuid"
}
```
```json
{
  "id": "e72c9a40-...-uuid",
  "name": "storage-vol-01",
  "total_hdd": 500,
  "used_hdd": 0,
  "free_hdd": 500,
  "is_alive": true,
  "created_at": "2026-06-27T12:00:00Z"
}
```

**Resize a virtual disk**

```
POST /iaas/virtual-disk-images/{id}/do/resize
```
```json
{
  "size": 100
}
```

**List virtual disks**

```
GET /iaas/virtual-disk-images
```

## Related Features

- [Virtual Machines](virtual-machines.md) — disks attach to and detach from a VM
- [Image Library](image-library.md) — VM and ISO images are stored on repository-backed storage
- [Backup & Disaster Recovery](backup-and-disaster-recovery.md) — backups are stored as repository images on storage
