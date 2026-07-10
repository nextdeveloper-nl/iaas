# Datacenters & Infrastructure

Behind every virtual machine is a hierarchy of physical infrastructure — datacenters, cloud nodes, and compute hosts — that the platform manages so resources land on the right hardware in the right location automatically.

## Key Capabilities

- Choose which datacenter (geographic location) a resource is provisioned in
- Datacenters report tier level, uptime guarantee, and physical infrastructure details
- Cloud nodes represent a logical site or facility within a datacenter
- Compute pools group hypervisor hosts together for a given virtualization type
- Compute members (hypervisor hosts) report live CPU, RAM, and running VM counts
- Edge nodes and maintenance mode are tracked per cloud node

## Datacenters

A datacenter is the top-level physical location — it records geographic coordinates, tier classification, guaranteed uptime, and infrastructure details like power redundancy and cooling. Choosing a datacenter when provisioning a resource determines where in the world it physically runs.

## Cloud Nodes

A cloud node sits inside a datacenter and represents a deployable site — it can be flagged as an edge node (for resources that need to be closer to end users) and can be put into maintenance mode, which the platform accounts for when deciding where new resources should go.

## Compute Pools and Compute Members

A compute pool groups hypervisor hosts that share the same virtualization technology and provisioning rules, with a total CPU/RAM capacity for the pool as a whole. Each compute member is an individual hypervisor host within a pool — it reports real-time figures for total and used CPU/RAM, how many VMs are running versus halted, and whether it's currently healthy and reachable. This is what the platform uses to decide which physical host a new VM should land on.

## API Examples

**List datacenters**

```
GET /iaas/datacenters
```
```json
{
  "id": "a1b2c3d4-...-uuid",
  "name": "Frankfurt DC-1",
  "tier_level": 4,
  "guaranteed_uptime": "99.95",
  "is_active": true
}
```

**List compute pools in a datacenter**

```
GET /iaas/compute-pools?filter[iaas_datacenter_id]=a1b2c3d4-...-uuid
```

**Check a compute member's current resource usage**

```
GET /iaas/compute-members/{id}
```
```json
{
  "id": "9f1e...-uuid",
  "name": "hypervisor-prod-01",
  "total_cpu": 32,
  "used_cpu": 24,
  "total_ram": 256,
  "used_ram": 200,
  "running_vm": 18,
  "is_alive": true
}
```

## Related Features

- [Virtual Machines](virtual-machines.md) — every VM runs on a specific compute member within a compute pool
- [Networking](networking.md) — networks are provisioned per cloud node/datacenter
- [Resource Management & Quotas](resource-management-and-quotas.md) — capacity and provisioning limits at the pool level
