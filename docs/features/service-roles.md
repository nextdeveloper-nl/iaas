# Service Roles

Service roles let a virtual machine come pre-configured with common software already installed — MySQL, PostgreSQL, Tailscale, and more — instead of logging in after boot to install it yourself. Pick the roles you want when you create a VM (or add them later), and the platform installs and configures them automatically the next time the VM boots.

## Key Capabilities

- Choose one or more service roles when creating a VM, or add/remove them from an existing VM
- Each role can be configured per VM (for example, a specific version) using sensible defaults when you don't specify one
- Roles are installed using the same trusted first-boot configuration process that already sets your VM's hostname, password, and SSH keys — no extra agent or manual step required
- The catalog of available roles is centrally maintained and can grow over time without any change to how you use them
- Requesting a role that doesn't exist (or has been retired) is rejected immediately with a clear error, rather than silently doing nothing

## How It Works

Every VM applies its configuration once during first boot (and again on every subsequent boot, so it's always self-healing) using a small, versioned set of automation scripts. Service roles plug into that same process: when you enable a role, the platform includes that role's install/configuration steps in the VM's boot configuration, and it's applied automatically.

Because this runs at boot time, enabling a new service role on an already-running VM takes effect the next time that VM (re)boots — there's no live, in-place installation while it's running.

## Choosing Roles

Available roles are looked up from a shared catalog, so the exact list can change over time as new roles are added. Currently available roles include:

- **mysql** — installs and enables a MySQL server, optionally setting the root password
- **postgresql** — installs and configures a PostgreSQL server, optionally setting the superuser password
- **tailscale** — installs and enables Tailscale, optionally joining your tailnet automatically if an auth key is provided

Each role can be given its own configuration when you enable it (for example, a password or auth key). Anything you don't specify falls back to the role's default configuration.

## Per-VM Configuration

Service roles are configured per VM — enabling `mysql` on one server has no effect on any other server. If a role is later retired from the catalog, it's automatically dropped from that VM's configuration rather than causing errors.

## API Examples

**Create a VM with service roles enabled**

```
POST /iaas/virtual-machines
```
```json
{
  "name": "app-server-01",
  "ram": 4096,
  "iaas_compute_pool_id": "5f0c2b3a-...-uuid",
  "iaas_repository_image_id": "9a31d4e0-...-uuid",
  "service_roles": {
    "mysql": {},
    "postgresql": {
      "config": {
        "version": "16"
      }
    }
  }
}
```

**Add or update service roles on an existing VM**

```
PATCH /iaas/virtual-machines/{id}
```
```json
{
  "service_roles": {
    "mysql": {}
  }
}
```

**See which roles are currently enabled on a VM**

```
GET /iaas/virtual-machines/{id}
```
```json
{
  "id": "8d2e0a1b-...-uuid",
  "name": "app-server-01",
  "service_roles": {
    "mysql": {
      "enabled": true,
      "config": {}
    },
    "postgresql": {
      "enabled": true,
      "config": {
        "version": "16"
      }
    }
  }
}
```

**Browse the catalog of available roles**

```
GET /iaas/ansible-roles
```

## Related Features

- [Virtual Machines](virtual-machines.md) — service roles are configured as part of a VM's boot configuration
- [Automation](automation.md) — environment variables and SSH keys are applied through the same first-boot process
- [Monitoring & Alerts](monitoring-and-alerts.md) — monitoring-related service roles feed into platform health checks
