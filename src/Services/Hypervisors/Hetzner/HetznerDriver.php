<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\Hetzner;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Contracts\ProvisioningCapableInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\VirtualMachinesMetadataService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Hetzner Cloud driver - fourth external-provider backend in the multi-cloud aggregator
 * plan (own infra + DigitalOcean + Ilkbyte + Vultr + Hetzner in one panel). Confirmed
 * against the OpenAPI spec at
 * raw.githubusercontent.com/api-evangelist/hetzner/.../hetzner-servers-api-openapi.yml
 * (api.hetzner.cloud's own docs.hetzner.cloud site is a JS SPA that returns only nav
 * chrome to automated fetches, same class of problem as vultr.com's 403 - went to a
 * mirrored spec instead of guessing), plus one search confirming the base URL. Base URL
 * and status enum are high-confidence (directly quoted from the spec); anything not in
 * that spec is flagged inline as unconfirmed, same standard as the other three drivers.
 *
 * Cleanest of the four external providers so far: bearer-token auth (like
 * DigitalOcean/Vultr), a real change_type (resize) action (like Vultr, unlike Ilkbyte),
 * an explicit closed status enum (running/initializing/starting/stopping/off/deleting/
 * migrating/rebuilding/unknown - no guessing between differently-worded fields the way
 * DO/Vultr/Ilkbyte each needed), and a dedicated action-object async-polling model
 * (poll the action, not the resource - see waitUntilActive()) rather than repolling the
 * server itself.
 *
 * Confirmed endpoints: POST /servers (create), GET /servers (list), GET /servers/{id}
 * (show), DELETE /servers/{id}, POST /servers/{id}/actions/{poweron|poweroff|reboot|
 * shutdown|change_type}, GET /actions/{action_id} (poll).
 *
 * Not confirmed/found in the spec fetched: console/VNC access endpoint (same gap as the
 * other three drivers), and whether "server_type"/"image" values are systematic enough
 * to derive from cpu/ram - they're accepted as either a numeric id or a human name/slug
 * (e.g. "cx22"), but Hetzner's naming isn't a formula like DO's "s-Xvcpu-Ygb" - treated as
 * opaque, explicitly configured, same as Ilkbyte/Vultr's os_id rather than guessed.
 */
class HetznerDriver implements
    VirtualMachineAdapterInterface,
    ProvisioningCapableInterface,
    ResizeCapableInterface,
    ConfigurationIsoCapableInterface,
    HostSyncInterface
{
    private const API_BASE_URL_DEFAULT = 'https://api.hetzner.cloud/v1';

    public function __construct(private readonly array $config = [])
    {
    }

    //  ----- VirtualMachineAdapterInterface -----

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $this->action($vm, 'poweron');

        return $vm;
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->action($vm, $force ? 'poweroff' : 'shutdown');

        return $vm;
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->action($vm, 'reboot');

        return $vm;
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Hetzner servers have no pause concept - only poweron/poweroff/shutdown/reboot.');
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Hetzner servers have no resume concept - only poweron/poweroff/shutdown/reboot.');
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Hetzner servers have no suspend concept - only poweron/poweroff/shutdown/reboot.');
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        $server = $this->client($vm)->get("/servers/{$vm->hypervisor_uuid}")->throw()->json('server');

        return [
            'uuid' => (string) $server['id'],
            'power-state' => $server['status'] === 'running' ? 'running' : 'halted',
            'raw' => $server,
        ];
    }

    public function delete(VirtualMachines $vm): bool
    {
        $response = $this->client($vm)->delete("/servers/{$vm->hypervisor_uuid}");

        if (!$response->successful() && $response->status() !== 404) {
            $response->throw();
        }

        return true;
    }

    public function sync(VirtualMachines $vm): VirtualMachines
    {
        $data = $this->getHypervisorData($vm);

        $vm->update([
            'hypervisor_data' => $data['raw'],
            'status' => $data['power-state'],
        ]);

        return $vm->fresh();
    }

    public function listAll(): array
    {
        $computeMember = $this->requireComputeMemberFromConfig();

        return $this->client(null, $computeMember)->get('/servers')->throw()->json('servers') ?? [];
    }

    //  ----- ProvisioningCapableInterface -----

    public function mountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        return true;
    }

    public function unmountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        return true;
    }

    public function mountIsoRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        return true;
    }

    public function importFromImage(
        VirtualMachines $vm,
        ComputeMembers $computeMember,
        Repositories $repository,
        StorageVolumes $volume,
        RepositoryImages $image,
        bool $isLazyDeploy
    ): string {
        $location = $this->resolveLocation($computeMember);
        $serverType = $this->resolveServerType($vm->cpu, $vm->ram, $computeMember);
        $imageRef = $this->resolveImageRef($image);

        $userData = VirtualMachinesMetadataService::getCloudInitConfiguration($vm);

        $payload = [
            'name' => $vm->hostname ?: $vm->name,
            'server_type' => $serverType,
            'image' => $imageRef,
            'location' => $location,
            'user_data' => $userData,
            'start_after_create' => true,
        ];

        $result = $this->client($vm, $computeMember)->post('/servers', $payload)->throw()->json();

        $server = $result['server'];

        //  Confirmed async model: the create response carries both the server object
        //  (immediately usable id) and a separate "action" object to poll - poll the
        //  action, not the server, per the spec's documented pattern.
        if (isset($result['action']['id'])) {
            $this->waitForAction($vm, $computeMember, $result['action']['id']);
        }

        $server = $this->client($vm, $computeMember)->get("/servers/{$server['id']}")->throw()->json('server');

        $vm->update([
            'hypervisor_uuid' => (string) $server['id'],
            'hypervisor_data' => $server,
        ]);

        return (string) $server['id'];
    }

    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        $server = $this->client(null, $computeMember)->get("/servers/{$ref}")->throw()->json('server');

        return [
            'uuid' => (string) $server['id'],
            'power-state' => $server['status'] === 'running' ? 'running' : 'halted',
        ] + $server;
    }

    public function renameVirtualMachine(VirtualMachines $vm): bool
    {
        //  No rename endpoint confirmed in the spec fetched - the server's name was
        //  already set correctly at create time in importFromImage(), left as a no-op
        //  rather than guessed (Hetzner's PUT /servers/{id} for name changes wasn't
        //  covered in the servers-api spec file this driver was built against).
        return true;
    }

    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        //  Same reasoning as the other three drivers - user_data is create-time-only,
        //  already handled in importFromImage().
        return true;
    }

    public function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        //  primary_disk_size is on the server object itself (confirmed field name) -
        //  boot disk is implicit in server creation, sized by server_type. Extra Volumes
        //  are a separate Hetzner API resource (confirmed present in the create payload
        //  as an optional "volumes" array of ids) not implemented here - Phase B scope,
        //  same as the other three drivers.
        $server = $vm->hypervisor_data['raw'] ?? $vm->hypervisor_data ?? [];
        $diskSizeBytes = (int) (($server['primary_disk_size'] ?? 0) * 1024 * 1024 * 1024);

        $disks = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($disks as $index => $disk) {
            if ($index === 0) {
                $disk->update([
                    'size' => $diskSizeBytes,
                    'device_number' => 0,
                    'is_cdrom' => false,
                    'hypervisor_uuid' => (string) $vm->hypervisor_uuid,
                    'hypervisor_data' => $server,
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[HetznerDriver] Extra disk row for VM ' . $vm->uuid . ' left as draft - '
                . 'Volumes attach support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    public function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
        //  public_net (ipv4/ipv6) is confirmed on the server object; private_net is a
        //  confirmed array field for private-network attachment but its per-item shape
        //  wasn't in the spec excerpt fetched - mark the primary VirtualNetworkCards row
        //  synced from public_net only, additional NICs/private-network attach are
        //  Phase B, same pattern as the other three drivers.
        $server = $vm->hypervisor_data['raw'] ?? $vm->hypervisor_data ?? [];

        $cards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($cards as $index => $card) {
            if ($index === 0) {
                $card->update([
                    'device_number' => 0,
                    'hypervisor_uuid' => (string) $vm->hypervisor_uuid,
                    'hypervisor_data' => $server['public_net'] ?? [],
                    'status' => 'active',
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[HetznerDriver] Extra network card row for VM ' . $vm->uuid . ' left as draft - '
                . 'multi-NIC/private-network support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    //  ----- ResizeCapableInterface -----

    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines
    {
        if (!$vm->hypervisor_uuid) {
            return $vm;
        }

        $computeMember = VirtualMachinesService::getComputeMember($vm);
        $serverType = $this->resolveServerType($cpu, $ramInMb, $computeMember);

        //  Confirmed real endpoint: POST /servers/{id}/actions/change_type. Hetzner
        //  requires the server to be off for most type changes (not confirmed exactly
        //  which - left to the API to reject with a clear error rather than guessed
        //  power-cycling logic added here).
        $this->action($vm, 'change_type', ['server_type' => $serverType, 'upgrade_disk' => false]);

        return $vm;
    }

    //  ----- ConfigurationIsoCapableInterface -----

    public function regenerateConfigurationIso(VirtualMachines $vm): bool
    {
        //  user_data is create-time-only on Hetzner too - exists so GenerateCloudInitImage
        //  resolves this driver instead of falling back to its XenServer-only default.
        return true;
    }

    //  ----- HostSyncInterface -----
    //  No real host behind a synthetic Hetzner ComputeMembers row - see
    //  DigitalOceanDriver's HostSyncInterface section for the full reasoning, identical here.

    public function detectVersion(ComputeMembers $computeMember): string
    {
        return 'hetzner-api';
    }

    public function syncMember(ComputeMembers $computeMember): ComputeMembers
    {
        return $computeMember;
    }

    public function syncInterfaces(ComputeMembers $computeMember): ComputeMembers
    {
        return $computeMember;
    }

    public function syncNetworks(ComputeMembers $computeMember): ComputeMembers
    {
        return $computeMember;
    }

    public function syncStorageVolumes(ComputeMembers $computeMember): ComputeMembers
    {
        return $computeMember;
    }

    public function syncVirtualMachines(ComputeMembers $computeMember): ComputeMembers
    {
        return $computeMember;
    }

    public function syncStorageVolumeDisks(ComputeMembers $computeMember, StorageVolumes $volume): StorageVolumes
    {
        return $volume;
    }

    public function isPoolMember(ComputeMembers $computeMember): bool
    {
        return false;
    }

    //  ----- Internal helpers -----

    private function action(VirtualMachines $vm, string $name, array $extra = []): array
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $response = $this->client($vm, $computeMember)
            ->post("/servers/{$vm->hypervisor_uuid}/actions/{$name}", $extra)
            ->throw()
            ->json();

        if (isset($response['action']['id'])) {
            $this->waitForAction($vm, $computeMember, $response['action']['id']);
        }

        return $response['action'] ?? [];
    }

    /**
     * Polls Hetzner's dedicated action-tracking resource (GET /actions/{id}) until it
     * leaves the "running" status - a different async model from DigitalOcean/Vultr
     * (which are polled by repolling the resource itself), confirmed from the create
     * endpoint's documented "action" response field.
     */
    private function waitForAction(VirtualMachines $vm, ComputeMembers $computeMember, int $actionId): void
    {
        $maxAttempts = 30;
        $delaySeconds = 5;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $action = $this->client(null, $computeMember)->get("/actions/{$actionId}")->throw()->json('action');

            if (($action['status'] ?? null) === 'success') {
                return;
            }

            if (($action['status'] ?? null) === 'error') {
                $message = $action['error']['message'] ?? 'unknown error';
                throw new \RuntimeException("Hetzner action {$actionId} failed: {$message}");
            }

            sleep($delaySeconds);
        }

        throw new \RuntimeException("Hetzner action {$actionId} did not complete within "
            . ($maxAttempts * $delaySeconds) . ' seconds.');
    }

    private function client(?VirtualMachines $vm = null, ?ComputeMembers $computeMember = null)
    {
        $computeMember ??= $vm ? VirtualMachinesService::getComputeMember($vm) : $this->requireComputeMemberFromConfig();

        if (!$computeMember->agent_api_key) {
            throw new \RuntimeException("Compute member {$computeMember->uuid} has no agent_api_key configured - "
                . 'cannot authenticate to the Hetzner Cloud API.');
        }

        return Http::baseUrl($this->config['api_base_url'] ?? self::API_BASE_URL_DEFAULT)
            ->withToken(decrypt($computeMember->agent_api_key))
            ->acceptJson()
            ->asJson();
    }

    private function requireComputeMemberFromConfig(): ComputeMembers
    {
        $uuid = $this->config['compute_member_uuid'] ?? null;

        if (!$uuid) {
            throw new \RuntimeException('HetznerDriver::listAll() requires a compute_member_uuid in its '
                . 'config/virtualization.php platform config to know which project/region to list servers for.');
        }

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $uuid)->firstOrFail();
    }

    private function resolveLocation(ComputeMembers $computeMember): string
    {
        $location = $computeMember->computePools?->pool_data['location'] ?? null;

        if (!$location) {
            throw new \RuntimeException("ComputePools.pool_data['location'] is not set for compute member "
                . "{$computeMember->uuid} - required to know which Hetzner location to provision in (e.g. \"nbg1\", \"fsn1\", \"hel1\", \"ash\").");
        }

        return $location;
    }

    /**
     * Hetzner's "image" field accepts either a numeric id or a name/slug directly
     * (confirmed in the create-server spec), but the exact catalog values are still
     * account/product-specific - same explicit-config convention as the other drivers'
     * image mapping, via RepositoryImages.extra.
     */
    private function resolveImageRef(RepositoryImages $image): string
    {
        $extra = json_decode((string) $image->extra, true) ?? [];
        $ref = $extra['hetzner_image'] ?? null;

        if (!$ref) {
            throw new \RuntimeException("RepositoryImages '{$image->name}' has no hetzner_image set in its extra "
                . 'field - required to know which Hetzner image (id or slug, e.g. "ubuntu-22.04") to boot from.');
        }

        return (string) $ref;
    }

    /**
     * Maps cpu/ram to a Hetzner server_type name/id (e.g. "cx22"). Their naming isn't a
     * derivable formula the way DigitalOcean's is - explicitly configured via
     * ComputePools.pool_data.server_type_map, same "must configure, don't guess"
     * convention as Ilkbyte/Vultr's opaque catalog ids.
     */
    private function resolveServerType(int $cpu, int $ramInMb, ComputeMembers $computeMember): string
    {
        $map = $computeMember->computePools?->pool_data['server_type_map'] ?? ($this->config['size_map'] ?? []);
        $key = $cpu . ':' . $ramInMb;

        if (!isset($map[$key])) {
            throw new \RuntimeException("No Hetzner server_type configured for cpu:ram '{$key}' - set it in "
                . "ComputePools.pool_data['server_type_map'] (e.g. {\"2:4096\": \"cx22\"}). Hetzner's naming "
                . '(cx/cpx/ccx lines) is not a derivable formula.');
        }

        return $map[$key];
    }
}
