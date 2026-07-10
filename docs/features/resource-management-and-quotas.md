# Resource Management & Quotas

Every account on the platform operates within resource limits, and every compute pool has a finite (or intentionally over-provisioned) amount of capacity. This keeps usage predictable for both account holders and platform operators.

## Key Capabilities

- Each account can have explicit resource limits (CPU, RAM, storage, and similar dimensions)
- Accounts can be suspended, which blocks new provisioning without deleting existing resources
- Compute pools define their total CPU and RAM ceiling, with an optional over-booking ratio for efficient utilization
- Licences tie specific resources to a subscription, so entitlements are enforced consistently
- Service status (enabled/suspended) and billing mode (prepaid/postpaid) are tracked per account

## Account Limits

An account's `limits` define the ceiling on resources it's allowed to provision — for example, a maximum CPU or RAM total across all its VMs. If an account is suspended, the platform blocks new provisioning while leaving existing resources untouched, which is typically used for billing or policy enforcement.

## Compute Pool Capacity and Over-Booking

A compute pool's total CPU and RAM represent its real ceiling, but hosts within a pool can also be configured with a maximum over-booking ratio — allowing more virtual CPU/RAM to be allocated than physically exists, based on the assumption that not all VMs use their full allocation simultaneously. This is a standard cloud efficiency technique, tuned per pool to balance cost and headroom.

## Licences

A licence links a specific resource (a compute pool, a VM, or another object) to a subscription. This is how feature entitlements and paid add-ons are enforced — a resource only gets a capability if there's a corresponding active licence tied to the right subscription.

## API Examples

**Check an account's resource limits**

```
GET /iaas/accounts/{id}
```
```json
{
  "id": "1f2e3d4c-...-uuid",
  "limits": {
    "cpu": 64,
    "ram_gb": 256
  },
  "is_service_enabled": true,
  "is_suspended": false
}
```

**Create a licence linking a resource to a subscription**

```
POST /iaas/licences
```
```json
{
  "object_type": "ComputePool",
  "object_id": 42,
  "subscription_id": "7c9d...-uuid"
}
```

## Related Features

- [Datacenters & Infrastructure](datacenters-and-infrastructure.md) — compute pool capacity and over-booking
- [Virtual Machines](virtual-machines.md) — VM provisioning is constrained by account limits
