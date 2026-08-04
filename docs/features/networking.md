# Networking

The platform gives every account its own software-defined networks, so virtual machines can talk to each other privately, reach the internet over a public network, or sit behind a dedicated gateway and firewall — without you managing physical switches or cabling.

## Key Capabilities

- Create public, private, VPN, management, or DMZ networks
- Define an IP range, CIDR block, and DNS servers per network
- Attach virtual network cards (NICs) to a VM and assign IP addresses to them
- Reserve specific IP addresses so they're never auto-assigned
- Put a network behind a dedicated gateway/firewall appliance
- Run DHCP on a network so VMs get addresses automatically
- Set bandwidth limits per network or per network card

## Network Types

A network can be marked public (internet-routable), private (internal-only), a VPN network, a management network, or a DMZ. This lets you segment workloads — for example, keeping a database on a private network while only the web tier sits on a public-facing one.

## Virtual Network Cards and IP Addresses

A virtual network card (NIC) is what actually connects a VM to a network. Each NIC gets its own MAC address and can be attached to or detached from a network independently of the VM's power state in most cases. IP addresses are managed as their own resource: they can be reserved ahead of time, assigned a custom MAC address, and are tracked with a history of past assignments so you can audit when an IP moved between resources.

## Gateways and DHCP

Networks can be paired with a gateway — a firewall/routing appliance that controls what traffic enters or leaves the network — and with a DHCP server that hands out IP addresses to VMs automatically as they boot, instead of requiring static configuration on every machine.

## Bandwidth Control

Both networks and individual network cards support bandwidth limits, so a single noisy VM can't saturate the network for everyone else sharing it.

## API Examples

**Create a network**

```
POST /iaas/networks
```
```json
{
  "name": "prod-network",
  "is_public": true,
  "cidr": "10.0.1.0/24",
  "ip_addr_range_start": "10.0.1.10",
  "ip_addr_range_end": "10.0.1.254",
  "dns_nameservers": ["8.8.8.8", "8.8.4.4"],
  "speed_limit": 1000
}
```
```json
{
  "id": "c4a9e210-...-uuid",
  "name": "prod-network",
  "is_public": true,
  "is_vpn": false,
  "is_dmz": false,
  "cidr": "10.0.1.0/24",
  "created_at": "2026-06-27T12:00:00Z"
}
```

**Attach a network card to a VM**

```
POST /iaas/virtual-network-cards
```
```json
{
  "iaas_virtual_machine_id": "8d2e0a1b-...-uuid",
  "iaas_network_id": "c4a9e210-...-uuid"
}
```

**List IP addresses on a network**

```
GET /iaas/ip-addresses?filter[iaas_network_id]=c4a9e210-...-uuid
```

## Related Features

- [Virtual Machines](virtual-machines.md) — networks and NICs attach directly to a VM
- [Monitoring & Alerts](monitoring-and-alerts.md) — network throughput and health metrics
- [Datacenters & Infrastructure](datacenters-and-infrastructure.md) — networks are provisioned per cloud node/datacenter
