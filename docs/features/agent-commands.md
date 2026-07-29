# VM Agent Commands

Every VM can run an in-guest agent that stays connected over NATS and reports health (CPU, memory, disk, network) without needing SSH configured. The same agent also accepts **commands** — service management, system info, disk resize after a volume expand, and so on. This page covers how to discover what a given VM's agent can do and how to trigger it from the API.

## Key Capabilities

- Discover the exact set of operations a VM's agent currently supports
- Run a command asynchronously and poll for the result later
- Run a command synchronously and get the result inline (with a timeout)
- Every command is recorded so its status and result can be looked up independently of the original request
- Pull a VM's full command history (with results) in one call, already scoped to that VM

## How Discovery Works

The agent periodically reports its own capability list back over NATS (a `capabilities` event). The platform stores that list verbatim on the VM's `available_operations['agent']` attribute — the platform does **not** hardcode which operations exist. This means:

- The set of available operations can differ per VM (depends on agent version/OS/build)
- New agent operations show up automatically the next time the agent reports capabilities — no platform deploy needed
- If a VM has never reported capabilities yet (e.g. it just booted), the list will be empty until the agent's next heartbeat

Each entry in the list looks like:

```json
{
  "operation": "services.restart",
  "description": "Restart a systemd service (stop then start).",
  "params": [
    { "name": "name", "type": "string", "required": true, "description": "Service name to restart." }
  ]
}
```

## Triggering a Command

### 1. List what's available

```
GET /iaas/virtual-machines/{id}/agent/operations
```

```json
{
  "vm_uuid": "8d2e0a1b-...-uuid",
  "operations": {
    "agent": [
      { "operation": "services.list", "description": "List all loaded systemd services on the machine." },
      { "operation": "services.restart", "description": "Restart a systemd service (stop then start).", "params": [ { "name": "name", "type": "string", "required": true } ] },
      { "operation": "system.metrics", "description": "Return a full resource snapshot (CPU, memory, disk, network)." },
      { "operation": "disk.resize", "description": "Grow a filesystem to fill its underlying disk after a volume expand.", "params": [ { "name": "name", "type": "string", "required": false } ] }
    ]
  }
}
```

### 2. Run it — async (fire and poll)

Use this for anything that isn't instant, or when the caller doesn't want to hold a connection open.

```
POST /iaas/virtual-machines/{id}/agent/{operation}
```

Any request body fields are passed straight through as the operation's `params`. For example:

```
POST /iaas/virtual-machines/8d2e0a1b-.../agent/services.restart
```
```json
{
  "name": "nginx"
}
```

Returns `202` immediately:

```json
{
  "command_uuid": "3fa1c2e0-...-uuid",
  "status": "sent"
}
```

Poll the command's status and result via the shared agent-commands endpoint — see [Tracking Commands](#tracking-commands) below for the full lookup/listing/filtering options.

### 3. Run it — sync (block for the result)

Use this when the caller wants the result in the same request/response cycle — e.g. a "run now" button in the dashboard. This blocks server-side until the agent replies or the timeout elapses.

```
POST /iaas/virtual-machines/{id}/agent/{operation}/sync
```
```json
{
  "name": "nginx",
  "timeout_s": 15
}
```

`timeout_s` is optional (defaults to 10 seconds) and is stripped out of the params sent to the agent — everything else in the body is passed through.

Returns `200` with the result inline:

```json
{
  "operation": "services.restart",
  "result": { "status": "completed", "output": { "name": "nginx", "active": true } }
}
```

Or `504` if the agent doesn't reply in time:

```json
{
  "message": "Timed out waiting for agent reply.",
  "error_code": "ERROR-AGENT-TIMEOUT"
}
```

### Invalid operations

If you request an operation that isn't in the VM's current `available_operations['agent']` list, both the async and sync endpoints reject it with the current allow-list included in the error, instead of forwarding a command the agent doesn't understand:

```json
{
  "message": "Operation 'disk.wipe' is not available for this VM agent. Available: services.list, services.restart, system.metrics, disk.resize",
  "error_code": "ERROR-INVALID-OPERATION",
  "available_operations": { "agent": [ /* ... */ ] }
}
```

## Tracking Commands

Every command dispatched to an agent (whether via the async or sync endpoint) is recorded in a shared table owned by the Events module, independent of which resource type (`vm`, `compute`, ...) sent it. You look these up under `/events/agent-commands` — **note the plural `events`**, not `event`; hitting the singular path returns a "route could not be found" error rather than a 404 on a missing record, since the route itself doesn't exist.

### Look up a single command

Use the `command_uuid` returned from the async dispatch call:

```
GET /events/agent-commands/{command_uuid}
```

```json
{
  "id": "3fa1c2e0-...-uuid",
  "agent_type": "vm",
  "operation": "services.restart",
  "params": { "name": "nginx" },
  "status": "completed",
  "result": { "name": "nginx", "active": true },
  "error": null,
  "sent_at": "2026-07-29T10:00:00Z",
  "completed_at": "2026-07-29T10:00:02Z"
}
```

The `status` field moves through `pending` → `sent` → `completed` / `failed` / `rejected` as the agent processes and replies. Once `completed`, `result` holds the agent's output; if `failed` or `rejected`, `error` holds the failure message instead.

### List commands for a specific VM

This is the one to use for a "command history" view in the dashboard — it's scoped to a single VM's own commands, so you don't need to filter the shared table by UUID yourself:

```
GET /iaas/virtual-machines/{id}/agent/commands
```

```json
{
  "data": [
    {
      "id": "3fa1c2e0-...-uuid",
      "agent_type": "vm",
      "operation": "services.restart",
      "params": { "name": "nginx" },
      "status": "completed",
      "result": { "name": "nginx", "active": true },
      "error": null,
      "sent_at": "2026-07-29T10:00:00Z",
      "completed_at": "2026-07-29T10:00:02Z"
    }
  ]
}
```

It accepts the same query-string filters as the shared `/events/agent-commands` listing below (`status`, `operation`, date ranges, `paginate`, ...) — `agent_type`/`agent_uuid` are already forced to this VM and can't be overridden by the query string.

### List and filter commands (any agent type)

```
GET /events/agent-commands
```

Supported filters (query string parameters), from `AgentCommandsQueryFilter`:

| Filter | Matches |
| --- | --- |
| `agent_type` | Partial match on agent type (`vm`, `compute`, ...) |
| `agent_uuid` | Exact match on the owning resource's UUID (e.g. a specific VM) |
| `operation` | Partial match on operation name (e.g. `services.restart`) |
| `status` | Partial match on status (`pending`, `sent`, `completed`, `failed`, `rejected`) |
| `error` | Partial match on the error message |
| `sent_at_start` / `sent_at_end` | Date range on when the command was sent |
| `completed_at_start` / `completed_at_end` | Date range on when the command completed |
| `created_at_start` / `created_at_end` | Date range on when the command row was created |

Example — find recently failed restart commands for a specific VM without using the VM-scoped endpoint above:

```
GET /events/agent-commands?agent_type=vm&agent_uuid=8d2e0a1b-...-uuid&operation=services.restart&status=failed
```

## Compute Host Agents

Compute hosts (`ComputeMembers` — the hypervisor nodes VMs run on) have the same underlying mechanism (`available_operations['agent']`, the same command dispatch/tracking pipeline) but are not yet wired up to their own HTTP endpoints — there's no `/compute-members/{id}/agent/...` route today. The service-layer plumbing (`ComputeMemberAgentCommandService`) already exists, so adding the equivalent controller/routes is a small follow-up if host-level agent commands are needed from the dashboard.

## API Examples

**List a VM's agent operations**

```
GET /iaas/virtual-machines/{id}/agent/operations
```

**Restart a service asynchronously**

```
POST /iaas/virtual-machines/{id}/agent/services.restart
```
```json
{ "name": "nginx" }
```

**Get a live system metrics snapshot, waiting for the reply**

```
POST /iaas/virtual-machines/{id}/agent/system.metrics/sync
```

**List a VM's command history**

```
GET /iaas/virtual-machines/{id}/agent/commands
```

**Check on an async command**

```
GET /events/agent-commands/{command_uuid}
```

**List failed commands for a given operation**

```
GET /events/agent-commands?operation=services.restart&status=failed
```

## Related Features

- [Virtual Machines](virtual-machines.md) — the VM lifecycle this agent runs alongside
- [Monitoring & Alerts](monitoring-and-alerts.md) — health checks driven by the same agent's heartbeat/telemetry
- [Datacenters & Infrastructure](datacenters-and-infrastructure.md) — compute hosts, which share the same underlying agent-command mechanism
