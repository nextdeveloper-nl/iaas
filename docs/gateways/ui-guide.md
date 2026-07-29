# Gateways — UI Guide

Guidance for whoever builds the control-panel screens for this feature. Written from
the API surface documented in `changes-and-usage.md` — read that first for exact
endpoints/fields; this doc is about what to build on top of it and why.

## Where this fits

A gateway isn't a resource users create standalone — it's always attached to a
Network, and in the common case (auto-provisioning on network creation) users won't
consciously "create" one at all. Design around that:

- Surface the gateway **on the Network's detail page**, not as a top-level nav item.
  A network either has a gateway or it doesn't — show a card/section that reflects
  whichever state applies.
- Only if a network doesn't have one, show a **"Provision a gateway"** action, which
  calls `POST /iaas/networks/{ref}/do/provision-gateway`.
- Once a gateway exists, that section becomes the gateway's own mini-dashboard
  (status, credentials, firewall rules, port forwards) — it doesn't need its own
  separate top-level page unless you want one for cross-network gateway management.

## Screen: VDC creation form — optional firewall image override

`POST /iaas/virtual-datacenters` now accepts an optional `iaas_repository_image_id`,
letting the user pick which firewall image provisions the auto-created gateway
instead of always getting the deployment default. This is a *separate* control from
anything on the Network detail page below — it only applies at creation time, before
a gateway exists.

- Default to **not showing this at all** — nothing changes for users who don't touch
  it, and the deployment default (whatever `leo.iaas.firewalls.<default_firewall_type>`
  resolves to) covers the common case.
- If you do surface it, put it behind an "Advanced" toggle or similar on the VDC
  creation form, not as a required field alongside name/cloud node.
- Populate the options from `GET /iaas/repository-images?filter[os]=firewall`,
  scoped to the cloud node the user already picked for the VDC (the images need to
  belong to a repository attached to that specific cloud node — an image valid on
  one cloud node may not be available on another). If the endpoint doesn't yet
  support filtering repository images by cloud node directly, fall back to fetching
  the cloud node's repositories first, then filtering images by
  `iaas_repository_id`.
- Show each option as whatever's human-readable on the image (`name`, or
  `distro`/`version`) — not the raw uuid.
- If the field is left blank, omit `iaas_repository_image_id` from the request
  entirely rather than sending an empty string — the backend treats "not present" as
  "use the default", not "explicitly nothing."
- Failure mode is the same shape as any other gateway-provisioning failure: the VDC
  itself is still created successfully even if the chosen image turns out to be
  invalid (removed, wrong repository, etc.) by the time the queued job runs — the
  network just ends up without a gateway and a system comment explaining why (see
  "State 4" below). There's no synchronous validation of the image at request time
  beyond what the `exists:iaas_repository_images,uuid` rule on the request checks
  (i.e. the uuid exists at all — not that it's a firewall image or available on that
  cloud node).

## Screen: Network detail → Gateway section

### State 1 — No gateway

```
┌─────────────────────────────────────────────┐
│  Firewall Gateway                            │
│                                               │
│  This network doesn't have a dedicated       │
│  firewall yet.                               │
│                                               │
│  [ Provision a Firewall ]                    │
└─────────────────────────────────────────────┘
```

- Button dispatches `POST /iaas/networks/{ref}/do/provision-gateway`.
- If more than one `gateway_type` is registered (multi-vendor future), this becomes a
  small select (default pre-selected from whatever the API/account default is) instead
  of a single button. For the pfSense-only launch, skip the select entirely.
- On submit, the action returns an `action_id` immediately (provisioning is async —
  the VM has to be built and booted). Move straight to State 2.

### State 2 — Provisioning (VM booting, credentials not ready yet)

```
┌─────────────────────────────────────────────┐
│  Firewall Gateway            ⏳ Provisioning │
│                                               │
│  Your pfSense firewall is booting up. This   │
│  usually takes a few minutes.                │
└─────────────────────────────────────────────┘
```

- Detect this state as: gateway row exists (`iaas_gateway_id` set on the network, or
  `GET /iaas/gateways/{ref}` returns a row), but `GET /iaas/gateways/{ref}/health`
  returns `reachable: false`, or `ssh_username` is still null on the gateway record.
- **Poll `GET /iaas/gateways/{ref}/health` every ~15–20s** while in this state. Stop
  polling once `api_auth_ok` is `true`, or after a generous timeout (~10 minutes —
  matches the job's own retry window) at which point show State 4 (see below).
- `api_auth_ok` no longer means "REST API auth succeeded" — it means the gateway VM's
  `pfsense.agent` responded to a `system.info` command over NATS. In practice this
  resolves faster than the old REST-bootstrap flow did, since there's no package
  install step on the box anymore, just the VM booting and the agent connecting.
- Don't block the rest of the network page on this — it's a background card, not a
  full-page spinner.

### State 3 — Ready

```
┌─────────────────────────────────────────────────────┐
│  Firewall Gateway                         ● Healthy  │
│                                                       │
│  IP Address     203.0.113.42                         │
│  Admin Login    admin  [Show password ▾]             │
│                                                       │
│  [ Firewall Rules (3) ]   [ Port Forwards (1) ]       │
└─────────────────────────────────────────────────────┘
```

- Health dot: green when `GET .../health` → `reachable: true && api_auth_ok: true`;
  amber if reachable but `api_auth_ok: false` (rare — the agent connected but the
  `system.info` command itself came back `failed`); red if unreachable (appliance may
  be down, or the VM agent hasn't connected yet — still show cached credentials,
  don't hide them).
- **There's no "API Access" row anymore.** Firewall/NAT/health management goes over
  the same NATS connection every VM agent uses — there's no separate REST API on the
  box, no token to display or copy. Only show SSH access (`ssh_username`/`ssh_password`),
  which is real console/shell access to the appliance, not something needed for the
  rule/NAT panels below to work.
- **Credentials are sensitive** — mask `ssh_password` by default behind a "Show"
  toggle / click-to-reveal, same treatment you'd give any other secret displayed in
  the panel (e.g. VM root passwords). Don't log it to browser console or analytics.
- Only show the credential fields at all to users who can see them via the API — a
  non-privileged `GET` still returns whatever's on the record (reads aren't stripped,
  only writes from `POST /iaas/gateways` are role-gated), so this is purely a display
  choice, not an access-control one enforced by the UI.
- "Firewall Rules" / "Port Forwards" open the sub-panels below (either inline
  expansion or a dedicated tab — either works, pick whatever matches this panel's
  existing pattern for VM sub-resources like disks/NICs).

### State 4 — Provisioning failed / timed out

```
┌─────────────────────────────────────────────┐
│  Firewall Gateway                ⚠ Problem   │
│                                               │
│  We couldn't finish setting up your          │
│  firewall. [ Retry ]  [ Contact support ]     │
└─────────────────────────────────────────────┘
```

- Trigger: health polling exceeded the timeout, or the gateway's VM ended up in a
  failed/lost state (check the VM's own status the same way any other VM failure is
  surfaced elsewhere in the panel).
- "Retry" re-dispatches `provision-gateway`. Don't auto-retry from the frontend beyond
  the backend's own retry budget — surface the failure and let the user decide.

## Screen/panel: Firewall Rules

A simple list + create form, scoped to one gateway.

| Column | Source field |
|---|---|
| Interface | `interface` (e.g. `wan`/`lan`/`opt1`) — pfSense's logical interface name, not the network's own name |
| Action | `action` (`pass` / `block` / `reject`) — render as a colored tag (pass=green, block=amber, reject=red) |
| Protocol | `protocol` |
| Source → Destination | `source` → `destination` |
| Port | `port` |
| Description | `description` |
| — | delete button, using the row's `ref` |

**Create form fields**, mapped 1:1 to `POST .../firewall-rules`:

- Interface — select (`wan`/`lan`/`opt1`/...), default `wan` if omitted. Populate the
  options from the gateway's own interfaces if you have that list available;
  otherwise a plain text field defaulting to `wan` is fine for the pfSense-only launch.
- Action — select: Pass / Block / Reject (`pass`/`block`/`reject`)
- Protocol — text or select (tcp/udp/icmp/any) — the API takes any string, but a
  constrained select avoids typos
- Source — text, default `any`
- Destination — text, default `any`
- Port — text (optional — leave blank for "any port"; a single port like `443` or a
  `low-high` range like `8000-9000` both work, it's passed through as-is)
- Description — text (optional, but strongly encourage it — this is the only
  human-readable label on a rule, and rules are otherwise opaque tuples)

No "edit" — deleting and recreating is simpler than modeling partial updates against
a driver that may not support them uniformly. Match this to whatever the design
system's existing "add tag / add rule"-style list pattern already does.

**Empty state**: "No firewall rules yet. Traffic follows pfSense's default policy
until you add one." — don't imply a rule is required to have working NAT/DHCP; it
isn't.

## Screen/panel: Port Forwards

Same shape as Firewall Rules, mapped to `POST .../port-forwards`:

| Column | Source field |
|---|---|
| Interface | `interface` — the interface traffic arrives on, usually `wan` |
| Protocol | `protocol` (tcp/udp) |
| External Port | `external_port` |
| Forwards to | `internal_ip` : `internal_port` |
| Description | `description` |
| — | delete, using `ref` |

**Create form**: Interface (select/text, default `wan`), Protocol (tcp/udp select),
External Port (integer, 1–65535), Internal IP (IP input — validate against the
network's own CIDR client-side as a nice-to-have, since a forward to an IP outside the
LAN silently does nothing useful), Internal Port (integer, 1–65535), Description
(optional).

**Port ranges aren't supported here yet** — unlike the firewall rule's `port` field,
`external_port`/`internal_port` are plain integers end-to-end (single port only). The
underlying agent operation accepts `low-high` ranges, but this pass doesn't expose
that; keep the inputs as single-port number fields, not range pickers.

## Cross-cutting concerns

- **Rule/NAT create-or-delete calls can now time out**, distinct from a validation or
  capability error — the request reaches the gateway VM's agent over NATS and the
  agent didn't reply in time (box unreachable, agent not running, etc.). Show this as
  "The gateway didn't respond in time — check its health status and try again," not
  as a form-field error, and don't auto-retry from the frontend; let the user retry
  manually once the health card shows reachable again.
- **A create/delete can also fail for a pfSense-side reason** (bad params the API
  itself didn't catch, an unknown `tracker` on delete, etc.) — these come back as a
  plain exception message from the backend (e.g. `pfSense agent operation
  'pfsense.firewall.create' failed: ...`); show it as-is, same treatment as the
  capability-error case below.
- **Don't build a "gateway_type" (driver/vendor) picker beyond a simple default for
  this launch.** The multi-vendor design exists so it *can* be added later without
  backend changes, but until a second driver actually ships, exposing that choice in
  the UI is dead weight. Revisit when a second `gateway_type` is registered in
  `config/gateway_drivers.php`. This is distinct from the firewall **image** picker
  (`iaas_repository_image_id`, see "VDC creation form" above) — that one's already
  wired and fine to expose (optionally) even with a single `gateway_type`.
- **Deleting a network deletes its gateway.** If your network delete confirmation
  dialog doesn't already say so, add a line: "This will also remove the network's
  firewall (VM, rules, and configuration)." — this is a real, hard-to-reverse action
  now that the cleanup bug is fixed; previously it silently orphaned everything, so
  there was nothing to warn about before.
- **Errors from rule/NAT endpoints can be capability errors, not validation errors** —
  e.g. `"Gateway type X does not support NAT/port-forward management"` if a future
  non-pfSense driver doesn't implement that capability. Surface these as plain
  messages (they're already human-readable), don't try to map them to form-field
  errors.
- **The health check is a real network call to the appliance**, not a cached DB flag —
  don't poll it faster than every ~15s per gateway, and don't poll at all for gateways
  the user isn't currently looking at.
