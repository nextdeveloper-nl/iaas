<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\Vultr;

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
 * Vultr driver - third external-provider backend in the multi-cloud aggregator plan (own
 * infra + DigitalOcean + Ilkbyte + Vultr in one panel). Confirmed against
 * github.com/vultr/govultr (the official Go client's instance.go, which lists literal
 * HTTP verbs/paths and struct fields) since api.vultr.com's own docs pages 403 to
 * automated fetches - not guessed from memory. Field/status-enum specifics not visible in
 * that source are flagged inline as unconfirmed, same standard applied to the other two
 * drivers.
 *
 * Shape is much closer to DigitalOceanDriver than IlkbyteDriver: bearer-token auth,
 * REST verbs, POST-to-subpath per-instance actions, a real PATCH-based resize endpoint
 * (unlike Ilkbyte, which has none), and a plan-slug naming convention
 * ("vc2-{cpu}c-{ramGB}gb") close enough to DigitalOcean's to reuse the same
 * best-effort-formula-plus-config-override approach.
 *
 * Confirmed endpoints (govultr instance.go): POST /v2/instances (create), GET
 * /v2/instances/{id} (show), GET /v2/instances (list), DELETE /v2/instances/{id},
 * POST /v2/instances/{id}/start, POST /v2/instances/{id}/halt, POST
 * /v2/instances/{id}/reboot, PATCH /v2/instances/{id} (resize, body: {"plan": "..."}).
 *
 * Not found in the source read: a distinct graceful-vs-force stop (only "halt" exists,
 * same ambiguity as IlkbyteDriver's single "shutdown" action), console access, and
 * pause/suspend (Vultr, like DO and Ilkbyte, only has power on/off - no pause concept).
 */
class VultrDriver implements
    VirtualMachineAdapterInterface,
    ProvisioningCapableInterface,
    ResizeCapableInterface,
    ConfigurationIsoCapableInterface,
    HostSyncInterface
{
    private const API_BASE_URL_DEFAULT = 'https://api.vultr.com/v2';

    public function __construct(private readonly array $config = [])
    {
    }

    //  ----- VirtualMachineAdapterInterface -----

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $this->client($vm)->post("/instances/{$vm->hypervisor_uuid}/start")->throw();

        return $vm;
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        //  Only "halt" was found in govultr's instance.go - no separate graceful-shutdown
        //  action, same ambiguity as IlkbyteDriver::stop().
        $this->client($vm)->post("/instances/{$vm->hypervisor_uuid}/halt")->throw();

        return $vm;
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->client($vm)->post("/instances/{$vm->hypervisor_uuid}/reboot")->throw();

        return $vm;
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Vultr instances have no pause concept - only start/halt/reboot.');
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Vultr instances have no resume concept - only start/halt/reboot.');
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Vultr instances have no suspend concept - only start/halt/reboot.');
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        $instance = $this->client($vm)->get("/instances/{$vm->hypervisor_uuid}")->throw()->json('instance');

        return [
            'uuid' => $instance['id'],
            //  "status" is the subscription lifecycle (pending/active/suspended/closed),
            //  "power_status" is only meaningful once status=active - confirmed via
            //  Vultr's own status-field documentation, not the Go client source.
            'power-state' => ($instance['power_status'] ?? null) === 'running' ? 'running' : 'halted',
            'raw' => $instance,
        ];
    }

    public function delete(VirtualMachines $vm): bool
    {
        $response = $this->client($vm)->delete("/instances/{$vm->hypervisor_uuid}");

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

        return $this->client(null, $computeMember)->get('/instances')->throw()->json('instances') ?? [];
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
        $region = $this->resolveRegion($computeMember);
        $plan = $this->resolvePlanSlug($vm->cpu, $vm->ram);
        $osId = $this->resolveOsId($image);

        $userData = VirtualMachinesMetadataService::getCloudInitConfiguration($vm);

        $payload = [
            'region' => $region,
            'plan' => $plan,
            'os_id' => $osId,
            'label' => $vm->hostname ?: $vm->name,
            'hostname' => $vm->hostname ?: $vm->name,
            'user_data' => base64_encode($userData),
            'backups' => 'disabled',
            'enable_ipv6' => false,
        ];

        $instance = $this->client($vm, $computeMember)
            ->post('/instances', $payload)
            ->throw()
            ->json('instance');

        $instance = $this->waitUntilActive($vm, $computeMember, $instance['id']);

        $vm->update([
            'hypervisor_uuid' => $instance['id'],
            'hypervisor_data' => $instance,
        ]);

        return $instance['id'];
    }

    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        $instance = $this->client(null, $computeMember)->get("/instances/{$ref}")->throw()->json('instance');

        return [
            'uuid' => $instance['id'],
            'power-state' => ($instance['power_status'] ?? null) === 'running' ? 'running' : 'halted',
        ] + $instance;
    }

    public function renameVirtualMachine(VirtualMachines $vm): bool
    {
        //  No dedicated rename action was in the govultr method list read - the update
        //  endpoint (PATCH /v2/instances/{id}) that resize() below uses likely accepts a
        //  "label" field too, but that wasn't confirmed in the source read, so this is
        //  left as a no-op rather than guessed. The label was already set correctly at
        //  create time in importFromImage() anyway.
        return true;
    }

    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        //  Same reasoning as DigitalOceanDriver/IlkbyteDriver - user_data is
        //  create-time-only, already handled in importFromImage().
        return true;
    }

    public function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        //  Boot disk is implicit in instance creation (sized by the plan), same model as
        //  DigitalOcean. Extra block storage is a separate Vultr product/API
        //  (Block Storage) not covered by this read of govultr's instance.go - left as
        //  draft and logged, Phase B scope.
        $instance = $vm->hypervisor_data['raw'] ?? $vm->hypervisor_data ?? [];
        $diskSizeBytes = (int) (($instance['disk'] ?? 0) * 1024 * 1024 * 1024);

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
                    'hypervisor_data' => $instance,
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[VultrDriver] Extra disk row for VM ' . $vm->uuid . ' left as draft - '
                . 'Block Storage support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    public function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
        //  main_ip/v6_main_ip are on the instance itself (no separate VIF/NIC list in the
        //  fields read from govultr) - mark the primary VirtualNetworkCards row synced
        //  from those. Additional NICs/VPC attachment (EnableVPC/AttachVPC were seen as
        //  create-time fields but the full VPC-attach lifecycle wasn't read) are Phase B.
        $instance = $vm->hypervisor_data['raw'] ?? $vm->hypervisor_data ?? [];

        $cards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($cards as $index => $card) {
            if ($index === 0) {
                $card->update([
                    'device_number' => 0,
                    'hypervisor_uuid' => (string) $vm->hypervisor_uuid,
                    'hypervisor_data' => $instance,
                    'status' => 'active',
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[VultrDriver] Extra network card row for VM ' . $vm->uuid . ' left as draft - '
                . 'multi-NIC/VPC support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    //  ----- ResizeCapableInterface -----

    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines
    {
        if (!$vm->hypervisor_uuid) {
            return $vm;
        }

        $plan = $this->resolvePlanSlug($cpu, $ramInMb);

        //  Unlike Ilkbyte, Vultr does have a real resize endpoint - PATCH with a new plan
        //  slug. Confirmed live from govultr's instance.go (Update Instance).
        $this->client($vm)->patch("/instances/{$vm->hypervisor_uuid}", ['plan' => $plan])->throw();

        return $vm;
    }

    //  ----- ConfigurationIsoCapableInterface -----

    public function regenerateConfigurationIso(VirtualMachines $vm): bool
    {
        //  user_data is create-time-only on Vultr too - exists so GenerateCloudInitImage
        //  resolves this driver instead of falling back to its XenServer-only default.
        return true;
    }

    //  ----- HostSyncInterface -----
    //  No real host behind a synthetic Vultr ComputeMembers row - see
    //  DigitalOceanDriver's HostSyncInterface section for the full reasoning, identical here.

    public function detectVersion(ComputeMembers $computeMember): string
    {
        return 'vultr-api';
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

    private function waitUntilActive(VirtualMachines $vm, ComputeMembers $computeMember, string $instanceId): array
    {
        $maxAttempts = 30;
        $delaySeconds = 5;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $instance = $this->client(null, $computeMember)->get("/instances/{$instanceId}")->throw()->json('instance');

            if (($instance['status'] ?? null) === 'active') {
                return $instance;
            }

            sleep($delaySeconds);
        }

        throw new \RuntimeException("Vultr instance {$instanceId} did not become active within "
            . ($maxAttempts * $delaySeconds) . ' seconds.');
    }

    private function client(?VirtualMachines $vm = null, ?ComputeMembers $computeMember = null)
    {
        $computeMember ??= $vm ? VirtualMachinesService::getComputeMember($vm) : $this->requireComputeMemberFromConfig();

        if (!$computeMember->agent_api_key) {
            throw new \RuntimeException("Compute member {$computeMember->uuid} has no agent_api_key configured - "
                . 'cannot authenticate to the Vultr API.');
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
            throw new \RuntimeException('VultrDriver::listAll() requires a compute_member_uuid in its '
                . 'config/virtualization.php platform config to know which account/region to list instances for.');
        }

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $uuid)->firstOrFail();
    }

    private function resolveRegion(ComputeMembers $computeMember): string
    {
        $region = $computeMember->computePools?->pool_data['region'] ?? null;

        if (!$region) {
            throw new \RuntimeException("ComputePools.pool_data['region'] is not set for compute member "
                . "{$computeMember->uuid} - required to know which Vultr region to provision in (e.g. \"ewr\").");
        }

        return $region;
    }

    /**
     * Maps our RepositoryImages row to Vultr's os_id (a numeric catalog id, same
     * "existing JSON blob column as driver payload" convention as the other two
     * drivers - not a new column).
     */
    private function resolveOsId(RepositoryImages $image): int
    {
        $extra = json_decode((string) $image->extra, true) ?? [];
        $osId = $extra['vultr_os_id'] ?? null;

        if ($osId === null) {
            throw new \RuntimeException("RepositoryImages '{$image->name}' has no vultr_os_id set in its extra "
                . 'field - required to know which Vultr OS to boot from.');
        }

        return (int) $osId;
    }

    /**
     * Best-effort cpu/ram -> Vultr plan-slug mapping using their "vc2" (Cloud Compute)
     * line's naming convention (e.g. "vc2-2c-4gb"). NOT validated against Vultr's live
     * /v2/plans list, and doesn't account for their other lines (vhf high-frequency, voc
     * optimized-cloud, bare metal, etc) which don't follow this exact pattern - a mismatch
     * surfaces as a clean 4xx from the create call, not a silent failure, but a config
     * override (size_map) is available for anything this formula gets wrong.
     */
    private function resolvePlanSlug(int $cpu, int $ramInMb): string
    {
        if (isset($this->config['size_map'][$cpu . ':' . $ramInMb])) {
            return $this->config['size_map'][$cpu . ':' . $ramInMb];
        }

        $ramInGb = (int) round($ramInMb / 1024);

        return "vc2-{$cpu}c-{$ramInGb}gb";
    }
}
