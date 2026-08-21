<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\DigitalOcean;

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
 * DigitalOcean driver - talks to the real DigitalOcean droplets API (v2) instead of a
 * hypervisor we own. Registered under the "digitalocean-api" virtualization string (see
 * config/virtualization.php). Every ComputeMembers row this driver is resolved for is a
 * synthetic "provider account/region" placeholder, not a physical host - see the
 * multi-cloud aggregator plan for why (no bin-packing, one seeded row per region).
 *
 * Deliberately NOT implemented (no DigitalOcean equivalent, mirrors the precedent already
 * set by XenServer82SshDriver::suspend()/listAll() throwing for the same reason):
 * - suspend()/pause()/resume() - droplets only support power_on/power_off, no
 *   suspend-to-disk or ACPI-pause concept.
 * - DiskCapableInterface/NetworkCapableInterface (extra volumes, VPC membership beyond
 *   the default) and ConsoleCapableInterface - deferred to Phase B/C of the aggregator
 *   plan, not required for Commit.php to complete a first import.
 */
class DigitalOceanDriver implements
    VirtualMachineAdapterInterface,
    ProvisioningCapableInterface,
    ResizeCapableInterface,
    ConfigurationIsoCapableInterface,
    HostSyncInterface
{
    private const API_BASE_URL_DEFAULT = 'https://api.digitalocean.com/v2';

    public function __construct(private readonly array $config = [])
    {
    }

    //  ----- VirtualMachineAdapterInterface -----

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $this->dropletAction($vm, 'power_on');

        return $vm;
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->dropletAction($vm, $force ? 'power_off' : 'shutdown');

        return $vm;
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->dropletAction($vm, $force ? 'power_cycle' : 'reboot');

        return $vm;
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('DigitalOcean droplets have no pause concept - only power_on/power_off.');
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('DigitalOcean droplets have no resume concept - only power_on/power_off.');
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('DigitalOcean droplets have no suspend concept - only power_on/power_off.');
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        $droplet = $this->client($vm)->get("/droplets/{$vm->hypervisor_uuid}")->throw()->json('droplet');

        return [
            'uuid' => (string) $droplet['id'],
            'power-state' => $droplet['status'] === 'active' ? 'running' : 'halted',
            'raw' => $droplet,
        ];
    }

    public function delete(VirtualMachines $vm): bool
    {
        $response = $this->client($vm)->delete("/droplets/{$vm->hypervisor_uuid}");

        //  204 on success, 404 if it's already gone - both mean "not there anymore".
        if ($response->status() !== 204 && $response->status() !== 404) {
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

        return $this->client(null, $computeMember)->get('/droplets')->throw()->json('droplets') ?? [];
    }

    //  ----- ProvisioningCapableInterface -----

    public function mountRepository(ComputeMembers $computeMember, Repositories $repository): bool
    {
        //  No repository/SR-mount concept for an external API provider - the image is
        //  referenced by slug directly in the droplet-create call, see importFromImage().
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
        $imageSlug = $this->resolveImageSlug($image);
        $region = $this->resolveRegion($computeMember);
        $sizeSlug = $this->resolveSizeSlug($vm->cpu, $vm->ram);

        //  DO has no separate "config ISO" mount step - user_data goes straight into the
        //  droplet-create payload. See regenerateConfigurationIso() below for why this is
        //  fetched fresh here rather than reading something GenerateCloudInitImage staged
        //  on disk (there's nothing to stage for this driver).
        $userData = VirtualMachinesMetadataService::getCloudInitConfiguration($vm);

        $payload = [
            'name' => $vm->hostname ?: $vm->name,
            'region' => $region,
            'size' => $sizeSlug,
            'image' => $imageSlug,
            'user_data' => $userData,
            'ipv6' => false,
            'backups' => false,
            'monitoring' => false,
        ];

        $droplet = $this->client($vm, $computeMember)
            ->post('/droplets', $payload)
            ->throw()
            ->json('droplet');

        //  Droplet creation is synchronous enough (the API call itself returns
        //  immediately with an id, "active" typically follows within under a minute) that
        //  we don't need XenServer's async-import/webhook dance - poll here instead of
        //  making Commit.php's caller pass is_lazy_deploy=false. See the aggregator plan's
        //  Phase A notes.
        $droplet = $this->waitUntilActive($vm, $computeMember, (string) $droplet['id']);

        $vm->update([
            'hypervisor_uuid' => (string) $droplet['id'],
            'hypervisor_data' => $droplet,
        ]);

        return (string) $droplet['id'];
    }

    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        $droplet = $this->client(null, $computeMember)->get("/droplets/{$ref}")->throw()->json('droplet');

        return [
            'uuid' => (string) $droplet['id'],
            'power-state' => $droplet['status'] === 'active' ? 'running' : 'halted',
        ] + $droplet;
    }

    public function renameVirtualMachine(VirtualMachines $vm): bool
    {
        $this->dropletAction($vm, 'rename', ['name' => $vm->uuid]);

        return true;
    }

    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        //  No live post-boot metadata channel (no xenstore-data equivalent) - DO droplets
        //  only accept user_data at create time, already handled in importFromImage(). A
        //  droplet created after this driver landed already has the API URL baked into its
        //  cloud-init user_data via VirtualMachinesMetadataService, so this is a no-op
        //  rather than a failure.
        return true;
    }

    public function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        //  The boot disk is implicit in droplet creation, not a separate resource - mark
        //  whichever draft VirtualDiskImages row is the boot disk (device_number 0) as
        //  synced using the droplet's reported disk size. Extra (non-boot) volumes are
        //  DiskCapableInterface scope (Phase B of the aggregator plan) - logged and left
        //  as draft here rather than silently claiming they're attached.
        $droplet = $vm->hypervisor_data['raw'] ?? $vm->hypervisor_data ?? [];
        $diskSizeBytes = (int) (($droplet['disk'] ?? 0) * 1024 * 1024 * 1024);

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
                    'hypervisor_data' => $droplet,
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[DigitalOceanDriver] Extra disk row for VM ' . $vm->uuid . ' left as draft - '
                . 'additional-volume support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    public function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
        //  DO attaches the default public (+ optional private) interface at create time,
        //  it's not a separately hot-pluggable VIF like XenServer - mark the primary
        //  VirtualNetworkCards row as synced from the droplet's reported networks. Extra
        //  NICs / explicit VPC selection are NetworkCapableInterface scope (Phase B).
        $droplet = $vm->hypervisor_data['raw'] ?? $vm->hypervisor_data ?? [];
        $networks = $droplet['networks']['v4'] ?? [];

        $cards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($cards as $index => $card) {
            if ($index === 0) {
                $card->update([
                    'device_number' => 0,
                    'hypervisor_uuid' => (string) $vm->hypervisor_uuid,
                    'hypervisor_data' => $networks,
                    'status' => 'active',
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[DigitalOceanDriver] Extra network card row for VM ' . $vm->uuid . ' left as draft - '
                . 'multi-NIC support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    //  ----- ResizeCapableInterface -----

    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines
    {
        //  Only called from Commit.php::setCpuRam() on every commit, including the very
        //  first one - skip if the droplet doesn't exist yet (importFromImage() already
        //  created it at the right size).
        if (!$vm->hypervisor_uuid) {
            return $vm;
        }

        $sizeSlug = $this->resolveSizeSlug($cpu, $ramInMb);

        //  DO's resize action is disk-inclusive-or-not; disk-inclusive is irreversible.
        //  Defaulting to false (CPU/RAM only, resizable both ways) - matches the "boot
        //  disk size is fixed at create time" model reconcileDiskConfiguration() assumes.
        $this->dropletAction($vm, 'resize', ['size' => $sizeSlug, 'disk' => false]);

        return $vm;
    }

    //  ----- ConfigurationIsoCapableInterface -----

    public function regenerateConfigurationIso(VirtualMachines $vm): bool
    {
        //  user_data is create-time-only on DigitalOcean - there is no live channel to
        //  push an updated cloud-init payload to a running droplet. This exists so
        //  GenerateCloudInitImage (called unconditionally from Commit.php) resolves a
        //  ConfigurationIsoCapableInterface driver and skips its XenServer-only fallback
        //  (VirtualMachinesXenService::updateConfigurationIso()) rather than crashing.
        return true;
    }

    //  ----- HostSyncInterface -----
    //
    //  There is no real host behind a synthetic DigitalOcean ComputeMembers row - these
    //  exist so Commit.php/Scan.php's HostSyncInterface call sites resolve without a new
    //  branch (see the aggregator plan's "synthetic ComputeMembers row" design). All of
    //  them are no-ops that return the row unchanged.

    public function detectVersion(ComputeMembers $computeMember): string
    {
        return 'digitalocean-api';
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
        //  DigitalOcean has no hypervisor-native pool-join concept - always a standalone
        //  "virtual pool" member in this codebase's terms (see HostSyncInterface docblock).
        return false;
    }

    //  ----- Internal helpers -----

    private function dropletAction(VirtualMachines $vm, string $type, array $extra = []): array
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        return $this->client($vm, $computeMember)
            ->post("/droplets/{$vm->hypervisor_uuid}/actions", ['type' => $type] + $extra)
            ->throw()
            ->json('action') ?? [];
    }

    private function waitUntilActive(VirtualMachines $vm, ComputeMembers $computeMember, string $dropletId): array
    {
        $maxAttempts = 30;
        $delaySeconds = 5;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $droplet = $this->client($vm, $computeMember)->get("/droplets/{$dropletId}")->throw()->json('droplet');

            if ($droplet['status'] === 'active') {
                return $droplet;
            }

            if ($droplet['status'] === 'errored') {
                throw new \RuntimeException("DigitalOcean droplet {$dropletId} entered an errored state during creation.");
            }

            sleep($delaySeconds);
        }

        throw new \RuntimeException("DigitalOcean droplet {$dropletId} did not become active within "
            . ($maxAttempts * $delaySeconds) . ' seconds.');
    }

    private function client(?VirtualMachines $vm = null, ?ComputeMembers $computeMember = null)
    {
        $computeMember ??= $vm ? VirtualMachinesService::getComputeMember($vm) : $this->requireComputeMemberFromConfig();

        if (!$computeMember->agent_api_key) {
            throw new \RuntimeException("Compute member {$computeMember->uuid} has no agent_api_key configured - "
                . 'cannot authenticate to the DigitalOcean API.');
        }

        return Http::baseUrl($this->config['api_base_url'] ?? self::API_BASE_URL_DEFAULT)
            ->withToken(decrypt($computeMember->agent_api_key))
            ->acceptJson()
            ->asJson();
    }

    /**
     * listAll() has no VM/ComputeMembers in hand to resolve credentials from - this driver
     * doesn't support that call yet (needs a config-supplied compute_member_uuid to scope
     * "list all droplets for which region/account"). Throws clearly rather than silently
     * returning an empty list.
     */
    private function requireComputeMemberFromConfig(): ComputeMembers
    {
        $uuid = $this->config['compute_member_uuid'] ?? null;

        if (!$uuid) {
            throw new \RuntimeException('DigitalOceanDriver::listAll() requires a compute_member_uuid in its '
                . 'config/virtualization.php platform config to know which account/region to list droplets for.');
        }

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $uuid)->firstOrFail();
    }

    private function resolveRegion(ComputeMembers $computeMember): string
    {
        $region = $computeMember->computePools?->pool_data['region'] ?? null;

        if (!$region) {
            throw new \RuntimeException("ComputePools.pool_data['region'] is not set for compute member "
                . "{$computeMember->uuid} - required to know which DigitalOcean region to provision in.");
        }

        return $region;
    }

    /**
     * Maps our RepositoryImages row to a DigitalOcean image slug. Expects
     * RepositoryImages.extra to be a JSON string containing a "digitalocean_slug" key -
     * same "existing JSON blob column as driver payload" convention as everywhere else in
     * this codebase, not a new column.
     */
    private function resolveImageSlug(RepositoryImages $image): string
    {
        $extra = json_decode((string) $image->extra, true) ?? [];
        $slug = $extra['digitalocean_slug'] ?? null;

        if (!$slug) {
            throw new \RuntimeException("RepositoryImages '{$image->name}' has no digitalocean_slug set in its "
                . "extra field - required to know which DigitalOcean image to boot from.");
        }

        return $slug;
    }

    /**
     * Best-effort cpu/ram -> DigitalOcean size-slug mapping using their naming
     * convention (e.g. "s-2vcpu-4gb"). NOT validated against DO's live /v2/sizes list -
     * a mismatch surfaces as a clean 4xx from the droplet-create call, not a silent
     * failure, but this should be replaced with a real lookup (cached from /v2/sizes)
     * before this driver is trusted for anything beyond Phase A testing.
     */
    private function resolveSizeSlug(int $cpu, int $ramInMb): string
    {
        if (isset($this->config['size_map'][$cpu . ':' . $ramInMb])) {
            return $this->config['size_map'][$cpu . ':' . $ramInMb];
        }

        $ramInGb = (int) round($ramInMb / 1024);

        return "s-{$cpu}vcpu-{$ramInGb}gb";
    }
}
