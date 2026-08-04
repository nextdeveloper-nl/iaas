# Update: gateways now carry `hostname` / `domain_type` / `common_domain_id`

**Date:** 2026-07-29
**Backend:** `nextdeveloper/iaas` v2.1.43
**Affects:** panel (frontend) — Gateway section on the Network detail page (see
`../ui-guide.md` §"Screen: Network detail → Gateway section")

## What changed and why

`iaas_gateways` had no way to record a domain/hostname for the appliance itself —
only the underlying firewall VM had `hostname`/`domain_type`/`common_domain_id`
(same pattern used across `iaas_virtual_machines`, `ComputeMembers`, `StorageMembers`).
Gateways needed the same fields so a gateway can expose its own DNS identity instead
of borrowing the VM's.

**Fields added to `iaas_gateways`** (nullable, no backfill — existing gateways have
all three `null` until explicitly set):

| Column | Type | Notes |
| --- | --- | --- |
| `hostname` | text | plain hostname string, e.g. `fw-01` |
| `domain_type` | text | free text, mirrors the `domain_type` convention on VMs |
| `common_domain_id` | FK → `common_domains` | sent/received as a UUID over the API, like every other FK on this platform |

## API surface

- `POST /iaas/gateways` and `PATCH /iaas/gateways/{id}` now accept `hostname`
  (nullable string), `domain_type` (nullable string), and `common_domain_id`
  (nullable, must be an existing `common_domains` UUID).
- `GET /iaas/gateways` / `GET /iaas/gateways/{id}` now return all three in the
  payload, with `common_domain_id` resolved to that domain's UUID (`null` if unset).
- Not gated behind `datacenter-admin`/`cloud-node-admin` the way `ssh_username` /
  `ssh_password` / `api_token` / `api_url` are on create — any caller who can update
  a gateway can set these, same as `name`.
- No change to `POST /iaas/networks/{ref}/do/provision-gateway` — auto-provisioning
  still doesn't set these fields, so a freshly provisioned gateway will have all
  three `null` until someone edits it.

## What to build

The natural home is the existing **State 3 — Ready** card in the Gateway section
(`../ui-guide.md` line ~105), which today only shows IP Address and Admin Login:

- Add a **Hostname** row (and Domain, once there's a domain picker component to
  reuse — check whatever the VM detail page already uses for its `common_domain_id`
  field, this should be the same component).
- Both should be editable inline (`PATCH /iaas/gateways/{id}`) — there's no
  restriction here like the "no ACL edit" case in access keys; a normal edit-in-place
  is fine.
- Since these are `null` on every gateway today, decide on empty-state copy (e.g.
  "No hostname set" with an inline "Add" affordance) rather than showing a blank row.
- No new screen needed — this is additive to the existing card, not a new flow.
