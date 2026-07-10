# Backup & Disaster Recovery

The platform protects your virtual machines with scheduled, automated backups and configurable retention policies, so a deleted file, a bad deployment, or hardware failure doesn't mean losing your data.

## Key Capabilities

- Schedule recurring backups by day of week, day of month, or a fixed time of day
- Define retention policies — how many backups to keep, and for how long
- Track each backup job against an explicit recovery target (RPO/RTO) and success rate (SLA)
- Get notified by email or webhook when a backup job finishes or fails
- Restore a VM from any retained backup
- Replicate backups to a secondary location for extra durability
- Automatically clean up backups that have exceeded their retention policy

## Backup Jobs and Schedules

A backup job defines what gets backed up (a VM or other resource), where the backups are stored, and how reliability is measured — including an expected Recovery Point Objective (how much data loss is acceptable, in hours) and Recovery Time Objective (how long a restore should take), plus an SLA target percentage and a maximum number of allowed consecutive failures before something needs attention. A backup schedule attaches a recurrence pattern to a job — for example, "every Sunday at 02:00" or "the 1st of every month."

## Retention Policies

A retention policy controls how long backups are kept: either a fixed number of days, or a fixed number of most-recent backups, whichever fits your needs. Once a backup falls outside the policy, it's automatically cleaned up to free storage.

## Restoring from Backup

Every backup tracks its own progress, start and end time, and the exact VM state it captured (CPU, RAM, disk contents). Restoring uses this snapshot to bring a VM back to the point the backup was taken.

## Notifications

Backup jobs can notify a list of email recipients or call a webhook URL when a backup starts, completes, or fails — so backup status doesn't require manually checking a dashboard.

## API Examples

**Create a backup retention policy**

```
POST /iaas/backup-retention-policies
```
```json
{
  "name": "30-day-retention",
  "keep_for_days": 30
}
```

**Create a backup job with recovery targets**

```
POST /iaas/backup-jobs
```
```json
{
  "name": "daily_vm_backup",
  "object_type": "VirtualMachine",
  "object_id": 123,
  "iaas_repository_id": "9a31d4e0-...-uuid",
  "iaas_backup_retention_policy_id": "8b1c...-uuid",
  "expected_rpo_hours": 24,
  "expected_rto_hours": 2,
  "sla_target_pct": 99.5
}
```

## Related Features

- [Virtual Machines](virtual-machines.md) — VMs are the primary backup target
- [Storage](storage.md) — backups are stored as repository images on storage
- [Image Library](image-library.md) — backups are tracked alongside other repository images
