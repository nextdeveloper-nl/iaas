# Gateways — Testing Guide

A practical, step-by-step guide to actually exercising the Gateways feature end to
end, plus how to verify each stage and what to check when a step doesn't behave as
expected. Pairs with `changes-and-usage.md` (what the API looks like) and
`ui-guide.md` (what a frontend should show) — this doc is about *proving it works*.

## Prerequisites — confirm before testing

1. **Queue workers are running for the right queues.** This feature's async steps
   are spread across three queues, all already configured in
   `.docker/supervisord.conf`:
   - `default` — `Actions\Networks\Create`, `Actions\Gateways\{ProvisionGateway,Delete,Commit}`
     (none of these override `$this->queue`, so they land here)
   - `iaas` — `Actions\VirtualMachines\{Commit,Delete,Start}` (the actual hypervisor
     provisioning/teardown)
   - `iaas-config` — `Jobs\Gateways\CollectGatewayCredentials`,
     `Jobs\VirtualMachines\GenerateCloudInitImage`

   If any of these three worker processes isn't running, the flow will look like
   it "hangs" at whichever step depends on that queue — check
   `php artisan queue:failed` and the worker processes themselves before assuming
   the code is broken.

2. **A pfSense CE 2.7.2 `RepositoryImages` row exists**, scoped to a repository
   attached to the cloud node you're testing against, matching
   `config('leo.iaas.firewalls.pfsense')` (`os=firewall`, `distro=pfsense ce`,
   `version=2.7.2` by default — check your actual `.env`/`config/leo.php` values).
   You confirmed this is already registered; if a test fails at the "provisioning
   firewall" step with a `CannotFindAvailableResourceException`, this is the first
   thing to re-check.

3. **The `provision-gateway` action row exists** (only needed if you're testing the
   *explicit* provisioning path, not the automatic one) — an `AvailableActions` row
   with `name = provision-gateway`, `input = NextDeveloper\IAAS\Networks`,
   `class = NextDeveloper\IAAS\Actions\Gateways\ProvisionGateway`. Run
   `php artisan leo:register-actions` once per environment to create it (it derives
   the row from the class itself) if it doesn't exist yet; confirm it exists before
   testing that path specifically.

## How to observe what's happening

Since most of this runs asynchronously in queued jobs, you won't see it in an HTTP
response. Three places to look, in order of usefulness:

- **System comments on the network/gateway** — every skip and failure path now
  leaves one (see `changes-and-usage.md`), via `CommentsService::createSystemComment()`.
  There's no dedicated `comments` relation on `Networks`/`Gateways` (confirmed by
  grep - no `comments()` method anywhere these models could expose it through the
  generic `relatedObjects` endpoint), so fetch through the top-level Comments
  resource instead, filtered by type:
  ```
  GET /comments?filter[object_type]=NextDeveloper\IAAS\Networks
  ```
  or with `php artisan tinker`:
  ```php
  \NextDeveloper\Commons\Database\Models\Comments::where('object_type', 'NextDeveloper\IAAS\Networks')
      ->latest()
      ->take(5)
      ->get(['body', 'object_id', 'created_at']);
  ```
  **Caveat**: `createSystemComment()` writes the object's `uuid` into `object_id`
  (confirmed by reading `CommentsService::createSystemComment()` directly), while
  the `Comments` model casts `object_id` as `integer` - assigning a UUID string to
  an integer-cast Eloquent attribute doesn't coerce it on write (verified: the raw
  attribute keeps the full UUID string; only *reading* it back re-applies the
  `(int)` cast, silently truncating it to `0`). In practice this means don't
  trust an exact `->where('object_id', $network->id)` filter to reliably match -
  filter by `object_type` and recency/body content instead, as above. This is
  pre-existing `CommentsService` behavior, not something introduced by this
  feature - worth knowing since it'll affect any comment lookup in this codebase,
  not just gateways.
- **Application logs** — every catch block in `Actions\Networks\Create` and
  `Actions\Gateways\ProvisionGateway` logs via `Log::error(...)` before leaving a comment;
  `CollectGatewayCredentials` logs each bootstrap attempt's outcome.
- **`php artisan queue:failed`** — if something threw *before* reaching one of the
  try/catch blocks we added (i.e. a bug rather than an anticipated failure mode),
  it'll land here since every worker runs with `--tries=1` (per
  `supervisord.conf`) except `CollectGatewayCredentials`, which sets its own
  `$tries = 10` on the job class — that value takes precedence over the worker's
  `--tries=1` flag, so it genuinely does get up to 10 attempts with backoff before
  giving up.

## Test 1 — Happy path: automatic provisioning

```
POST /iaas/virtual-datacenters
{ "name": "test-network", "iaas_cloud_node_id": "<a cloud node>" }
```

**Immediately after the response:**
```php
$network = \NextDeveloper\IAAS\Database\Models\Networks::where('uuid', '<returned uuid>')->first();
$network->iaas_gateway_id; // likely still null - the queued action hasn't run yet
```

**Within a few seconds** (once the `default` queue worker picks up
`Actions\Networks\Create`):
```php
$network->refresh();
$network->iaas_gateway_id; // should now be set

$gateway = \NextDeveloper\IAAS\Database\Models\Gateways::find($network->iaas_gateway_id);
$gateway->gateway_type;              // 'pfsense'
$gateway->iaas_virtual_machine_id;   // set
$gateway->ssh_username;              // still null - credentials aren't ready yet
```

**Poll health** until it flips:
```
GET /iaas/gateways/{gateway_ref}/health
```
Expect `{"reachable": false, ...}` for the first few minutes (VM booting), then
`{"reachable": true, "api_auth_ok": true}` once `CollectGatewayCredentials`
successfully bootstraps it (realistically 3–10 minutes after creation — the job's
first attempt is delayed 3 minutes on purpose).

**Confirm credentials landed:**
```
GET /iaas/gateways/{gateway_ref}
```
`ssh_username`, `ssh_password`, `api_token`, `api_url`, `ip_addr` should all be
populated. If they're still null after ~30 minutes (10 retry attempts at the
job's own backoff schedule: 60s, 60s, 120s, 120s, 180s, 180s, 300s×4 ≈ 27 min
total), something's wrong — check the comments/logs for the specific
`bootstrap()` failure message (SSH auth failure, no WAN IP, etc.).

## Test 2 — Opt-out (`create_gateway: false`)

```
POST /iaas/virtual-datacenters
{ "name": "test-no-gateway", "iaas_cloud_node_id": "<a cloud node>", "create_gateway": false }
```

Expect: `network.iaas_gateway_id` stays `null` forever, and a system comment
appears on the network: *"Gateway provisioning was skipped for this network
because create_gateway was set to false."* No VM, no `Gateways` row, nothing
queued beyond the network creation itself.

## Test 2b — Explicit firewall image (`iaas_repository_image_id`)

First, find a second `os=firewall` image to pick against (needs to differ from
whatever `leo.iaas.firewalls.<default_firewall_type>` would resolve to, so you can
tell the override actually took effect):
```
GET /iaas/repository-images?filter[os]=firewall
```

```
POST /iaas/virtual-datacenters
{
  "name": "test-custom-firewall",
  "iaas_cloud_node_id": "<a cloud node>",
  "iaas_repository_image_id": "<uuid of an os=firewall image on that cloud node>"
}
```

**Once the `default` queue worker picks up `Actions\Networks\Create`:**
```php
$network->refresh();
$gateway = \NextDeveloper\IAAS\Database\Models\Gateways::find($network->iaas_gateway_id);
$firewall = \NextDeveloper\IAAS\Database\Models\VirtualMachines::find($gateway->iaas_virtual_machine_id);

$firewall->iaas_repository_image_id; // should match the requested image's id, not the config default
```

Then verify failure handling: repeat with an `iaas_repository_image_id` that either
doesn't exist, isn't `os = 'firewall'`, or belongs to a repository not attached to
the chosen cloud node. Expect the same shape as Test 4 (network still created, no
gateway, system comment on the network) reading:
*"We could not provision a gateway for this network: The requested firewall image
\"\<uuid\>\" was not found, is not a firewall image, or is not available on cloud
node \"...\"."*

## Test 3 — Explicit provisioning

Create a network with `create_gateway: false` first (so it has no gateway, per
Test 2), then:
```
POST /iaas/networks/{ref}/do/provision-gateway
```
Verify the same way as Test 1 (poll health, check credentials).

Optionally pass a different type:
```json
{ "gateway_type": "pfsense" }
```
(no other type is registered yet, so this is mostly a no-op today, but confirms
the param is actually threaded through — check
`config/gateway_drivers.php`'s `platforms` array resolves it).

## Test 3b — Explicit provisioning with a specific firewall image

Same setup as Test 3 (a network with no gateway), but pass `iaas_repository_image_id`
the same way Test 2b does for the wizard:
```
POST /iaas/networks/{ref}/do/provision-gateway
{ "iaas_repository_image_id": "<uuid of an os=firewall image on that cloud node>" }
```
Verify the same way as Test 2b — `$firewall->iaas_repository_image_id` matches the
requested image, not the config default.

Then verify the guard: call `provision-gateway` again on the **same** network (which
now has a gateway from the call above):
```
POST /iaas/networks/{ref}/do/provision-gateway
{ "iaas_repository_image_id": "<any other firewall image>" }
```
Expect: refused. No second `Gateways` row, no second VM, a system comment reading
*"This network already has a gateway; delete it first (DELETE /iaas/gateways/{id})
before provisioning a new one."* — confirm via `Networks::find(...)->iaas_gateway_id`
still pointing at the original gateway, and no new `Gateways` row for this network.

## Test 3c — Replace an existing gateway's firewall image

Using the network + gateway from Test 3b:
```
DELETE /iaas/gateways/{gateway_ref}
```
Verify the same way as Test 7 (VM/disks/NICs gone, `iaas_gateway_id` back to `null`).
Then:
```
POST /iaas/networks/{ref}/do/provision-gateway
{ "iaas_repository_image_id": "<a *different* firewall image than Test 3b used>" }
```
Confirm a new gateway/VM exists with the second image, and — since both the deleted
and the new gateway's LAN NIC want `10.128.0.1/32` — that there's exactly **one**
`IpAddresses` row at that address for the network, not two left over:
```php
\NextDeveloper\IAAS\Database\Models\IpAddresses::where('iaas_network_id', $network->id)
    ->where('ip_addr', '10.128.0.1/32')
    ->count(); // should be 1
```

## Test 4 — Missing image error path

This is the one genuinely worth deliberately breaking to verify the fix. Temporarily
point the config at a version that doesn't exist in your `RepositoryImages` catalog:
```
IAAS_FIREWALL_VERSION=99.99.99
```
(or edit `config('leo.iaas.firewalls.pfsense.version')` directly for the test,
then restart whatever needs to pick up the config change).

Run Test 1 again. Expect:
- The network is still created successfully.
- A system comment (see "How to observe" above for how to fetch it) reading:
  *"We could not provision a gateway for this network: We cannot find a firewall
  image matching \"pfsense ce 99.99.99\" for gateway type \"pfsense\" on cloud
  node \"...\". Please consult to your cloud provider."*
- **No entry in `php artisan queue:failed`** for this — the exception is caught
  inside `Actions\Networks\Create::handle()`, so the action completes (with a
  message noting the failure) rather than dying. This is the behavior the fix was
  specifically for — before it, this scenario threw an uncaught `TypeError` that
  would show up in `queue:failed` with nothing user-facing to explain it.
- `network.iaas_gateway_id` stays `null`, no `Gateways` row, no VM.

Revert the config change afterward.

## Test 5 — Firewall rules

Once a gateway is healthy (`api_auth_ok: true`):
```
POST /iaas/gateways/{ref}/firewall-rules
{
  "action": "pass",
  "protocol": "tcp",
  "source": "any",
  "destination": "10.128.0.0/24",
  "port": "443",
  "description": "test rule"
}
```
```
GET /iaas/gateways/{ref}/firewall-rules
```
Confirm the created rule appears in the list with a `ref`, and — if you have
console/VPN access to the appliance — that it actually shows up in pfSense's own
Firewall → Rules UI. Then:
```
DELETE /iaas/gateways/{ref}/firewall-rules/{ref}
```
and confirm it's gone from both the list and pfSense's UI.

## Test 6 — Port forwards

Same shape as Test 5, against `/iaas/gateways/{ref}/port-forwards`:
```
POST /iaas/gateways/{ref}/port-forwards
{
  "protocol": "tcp",
  "external_port": 8443,
  "internal_ip": "10.128.0.10",
  "internal_port": 443,
  "description": "test forward"
}
```
Verify in pfSense's Firewall → NAT → Port Forward UI, then delete and re-verify.

## Test 7 — Deletion cascade

With a gateway fully provisioned:
```
DELETE /iaas/gateways/{ref}
```
Expect: the underlying VM, its disks and NICs are gone (check
`VirtualMachines::withTrashed()->find(...)` shows `deleted_at` set, or that the VM
no longer appears on the hypervisor), `Networks.iaas_gateway_id` on the owning
network is back to `null`, and the `Gateways` row itself is soft-deleted.

Then, on a **different** network+gateway pair, test the cascade the other
direction:
```
DELETE /iaas/networks/{ref}
```
Expect the same cleanup to happen automatically — this is the exact bug
(`Actions\Networks\Delete` was a dead stub) this pass fixed. Before, this call
would have left the firewall VM and `Gateways` row permanently orphaned; now it
shouldn't.

## Quick reference — expected timings

| Step | Typical time |
|---|---|
| Network + Gateway DB rows created | seconds (once `default` queue worker picks up the action) |
| VM imported + configured + started | roughly 1–3 minutes (`iaas` queue, depends on hypervisor/storage speed) |
| First credential bootstrap attempt | 3 minutes after provisioning (fixed delay) |
| Credentials populated (realistic case) | 3–10 minutes total |
| Credentials populated (worst case, VM slow to get an IP) | up to ~30 minutes before the job gives up |
