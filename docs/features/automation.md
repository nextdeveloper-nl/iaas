# Automation

Beyond launching a VM, the platform helps you configure it automatically — running Ansible playbooks, injecting environment variables, and managing SSH keys — so a freshly created server arrives ready to use instead of needing manual setup.

## Key Capabilities

- Run Ansible playbooks against VMs from a dedicated Ansible server
- Reuse a library of system playbooks for common tasks (hostname, passwords, SSH keys, disk resize, locale)
- Group environment variables together and apply them to one or many VMs
- Control the priority order in which environment variable groups are applied
- Manage SSH public keys and associate them with specific VMs

## Ansible Integration

An Ansible server (which can be a VM on the platform or an external machine you point to) executes playbooks against your VMs. Playbooks can be your own custom procedures or drawn from a built-in library of system playbooks that cover common day-one tasks. Every execution is tracked, so you can see what ran, when, and whether it succeeded.

## Environment Variables

Environment variable groups let you define a named set of key/value pairs once — for example, database credentials or feature flags for a "production" environment — and apply that whole group to any VM. A VM can have multiple groups attached, with a priority order controlling which value wins if the same key appears in more than one group. Sensitive values can be flagged as secrets.

## SSH Keys

SSH public keys can be registered once and attached to whichever VMs should accept them, rather than copying a key into every server individually.

## API Examples

**Create an environment variable group**

```
POST /iaas/env-var-groups
```
```json
{
  "name": "production-vars",
  "description": "Production environment settings"
}
```

**Assign an environment variable group to a VM**

```
POST /iaas/virtual-machine-env-var-groups
```
```json
{
  "iaas_virtual_machine_id": "8d2e0a1b-...-uuid",
  "iaas_env_var_group_id": "990e8400-...-uuid",
  "priority": 1
}
```

**Run an Ansible playbook against a VM**

```
POST /iaas/ansible-playbook-executions
```
```json
{
  "iaas_ansible_playbook_id": "1a2b...-uuid",
  "iaas_virtual_machine_id": "8d2e0a1b-...-uuid"
}
```

## Related Features

- [Virtual Machines](virtual-machines.md) — automation runs against VMs after they're created
- [Image Library](image-library.md) — cloud-init images pair with environment variables for first-boot setup
