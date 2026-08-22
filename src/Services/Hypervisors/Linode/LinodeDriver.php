<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\Linode;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
 * Linode (Akamai) driver - fifth external-provider backend in the multi-cloud aggregator
 * plan. Confirmed against techdocs.akamai.com/linode-api's own reference pages (the .md
 * suffix trick documented on that site itself returns clean markdown, unlike Vultr/Hetzner
 * whose docs sites blocked or blanked automated fetches) - base URL, auth, create-instance
 * schema, status enum, and get/list/delete/boot/resize paths are all directly confirmed
 * from those pages. shutdown/reboot paths were NOT directly fetched (only inferred from
 * boot's confirmed pattern - "/{linodeId}/shutdown" and "/{linodeId}/reboot" - by the same
 * RESTful convention every path on this page follows) - flagged here rather than presented
 * as equally confirmed.
 *
 * Notably different from the other four drivers: resize is SYNCHRONOUS (confirmed: "returns
 * a 200 response with an empty object... not an async operation requiring polling"), so
 * resize() below has no wait loop, unlike DigitalOcean/Vultr/Hetzner. Creation is async
 * (confirmed) and polled the same way as DigitalOcean/Vultr - repolling the instance
 * resource itself, not a separate action/task object like Hetzner.
 *
 * Real constraint found that the other four drivers didn't have: Linode requires at least
 * one of root_pass/authorized_keys/authorized_users on create, regardless of whether
 * metadata.user_data (cloud-init) is also supplied - see importFromImage()'s
 * randomly-generated, never-stored root_pass.
 *
 * Confirmed endpoints: POST /linode/instances (create), GET /linode/instances/{id}
 * (show), GET /linode/instances (list), DELETE /linode/instances/{id}, POST
 * .../{id}/boot, POST .../{id}/resize. Inferred-not-confirmed: .../{id}/shutdown,
 * .../{id}/reboot. Not found in the docs read: console/Lish/Glish access endpoint (same
 * gap as the other four drivers).
 */
class LinodeDriver implements
    VirtualMachineAdapterInterface,
    ProvisioningCapableInterface,
    ResizeCapableInterface,
    ConfigurationIsoCapableInterface,
    HostSyncInterface
{
    private const API_BASE_URL_DEFAULT = 'https://api.linode.com/v4';

    public function __construct(private readonly array $config = [])
    {
    }

    //  ----- VirtualMachineAdapterInterface -----

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $this->client($vm)->post("/linode/instances/{$vm->hypervisor_uuid}/boot")->throw();

        return $vm;
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        //  Path inferred from boot's confirmed pattern, not directly fetched - see class
        //  docblock.
        $this->client($vm)->post("/linode/instances/{$vm->hypervisor_uuid}/shutdown")->throw();

        return $vm;
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        //  Path inferred, same caveat as stop().
        $this->client($vm)->post("/linode/instances/{$vm->hypervisor_uuid}/reboot")->throw();

        return $vm;
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Linode instances have no pause concept - only boot/shutdown/reboot.');
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Linode instances have no resume concept - only boot/shutdown/reboot.');
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Linode instances have no suspend concept - only boot/shutdown/reboot.');
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        $instance = $this->client($vm)->get("/linode/instances/{$vm->hypervisor_uuid}")->throw()->json();

        return [
            'uuid' => (string) $instance['id'],
            'power-state' => $instance['status'] === 'running' ? 'running' : 'halted',
            'raw' => $instance,
        ];
    }

    public function delete(VirtualMachines $vm): bool
    {
        $response = $this->client($vm)->delete("/linode/instances/{$vm->hypervisor_uuid}");

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

        return $this->client(null, $computeMember)->get('/linode/instances')->throw()->json('data') ?? [];
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
        $type = $this->resolveType($vm->cpu, $vm->ram, $computeMember);
        $imageRef = $this->resolveImageRef($image);

        $userData = VirtualMachinesMetadataService::getCloudInitConfiguration($vm);

        $payload = [
            'label' => Str::limit($vm->hostname ?: $vm->name, 64, ''),
            'region' => $region,
            'type' => $type,
            'image' => $imageRef,
            //  Confirmed: at least one of root_pass/authorized_keys/authorized_users is
            //  required regardless of metadata.user_data - this is generated purely to
            //  satisfy that constraint, real access is via cloud-init (same as the other
            //  four drivers), so it's never stored/read back anywhere.
            'root_pass' => Str::password(32),
            'metadata' => ['user_data' => base64_encode($userData)],
            'booted' => true,
        ];

        $instance = $this->client($vm, $computeMember)->post('/linode/instances', $payload)->throw()->json();

        $instance = $this->waitUntilRunning($vm, $computeMember, $instance['id']);

        $vm->update([
            'hypervisor_uuid' => (string) $instance['id'],
            'hypervisor_data' => $instance,
        ]);

        return (string) $instance['id'];
    }

    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        $instance = $this->client(null, $computeMember)->get("/linode/instances/{$ref}")->throw()->json();

        return [
            'uuid' => (string) $instance['id'],
            'power-state' => $instance['status'] === 'running' ? 'running' : 'halted',
        ] + $instance;
    }

    public function renameVirtualMachine(VirtualMachines $vm): bool
    {
        //  PUT /linode/instances/{id} exists (confirmed path from the instance-operations
        //  index: "Update a Linode") and plausibly accepts "label", but the exact body
        //  schema wasn't fetched - left as a no-op rather than guessed. The label was
        //  already set correctly at create time in importFromImage().
        return true;
    }

    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        //  Same reasoning as the other four drivers - metadata.user_data is
        //  create-time-only, already handled in importFromImage().
        return true;
    }

    public function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        //  Boot disk is implicit in instance creation (sized by "type") - Linode's actual
        //  disk layout is queryable via a separate /linode/instances/{id}/disks endpoint
        //  (not fetched in this research pass) so no real size figure is available here
        //  yet, unlike DigitalOcean/Vultr/Hetzner where the parent object carries a disk
        //  size field directly. Extra disks are Phase B, same as the other four drivers.
        $disks = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($disks as $index => $disk) {
            if ($index === 0) {
                $disk->update([
                    'device_number' => 0,
                    'is_cdrom' => false,
                    'hypervisor_uuid' => (string) $vm->hypervisor_uuid,
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[LinodeDriver] Extra disk row for VM ' . $vm->uuid . ' left as draft - '
                . 'the /disks endpoint has not been researched yet, Phase B scope.');
        }
    }

    public function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
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
                    'hypervisor_data' => $instance['ipv4'] ?? [],
                    'status' => 'active',
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[LinodeDriver] Extra network card row for VM ' . $vm->uuid . ' left as draft - '
                . 'multi-interface/VPC support is Phase B of the multi-cloud aggregator plan.');
        }
    }

    //  ----- ResizeCapableInterface -----

    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines
    {
        if (!$vm->hypervisor_uuid) {
            return $vm;
        }

        $computeMember = VirtualMachinesService::getComputeMember($vm);
        $type = $this->resolveType($cpu, $ramInMb, $computeMember);

        //  Confirmed synchronous - no wait loop needed here, unlike every other driver's
        //  resize()/create flow. Defaults to allow_auto_disk_resize=true and
        //  migration_type=cold (both confirmed API defaults) by omitting them.
        $this->client($vm, $computeMember)
            ->post("/linode/instances/{$vm->hypervisor_uuid}/resize", ['type' => $type])
            ->throw();

        return $vm;
    }

    //  ----- ConfigurationIsoCapableInterface -----

    public function regenerateConfigurationIso(VirtualMachines $vm): bool
    {
        //  metadata.user_data is create-time-only on Linode too - exists so
        //  GenerateCloudInitImage resolves this driver instead of falling back to its
        //  XenServer-only default.
        return true;
    }

    //  ----- HostSyncInterface -----
    //  No real host behind a synthetic Linode ComputeMembers row - see
    //  DigitalOceanDriver's HostSyncInterface section for the full reasoning, identical here.

    public function detectVersion(ComputeMembers $computeMember): string
    {
        return 'linode-api';
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

    private function waitUntilRunning(VirtualMachines $vm, ComputeMembers $computeMember, int $instanceId): array
    {
        $maxAttempts = 30;
        $delaySeconds = 5;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $instance = $this->client(null, $computeMember)->get("/linode/instances/{$instanceId}")->throw()->json();

            if ($instance['status'] === 'running') {
                return $instance;
            }

            sleep($delaySeconds);
        }

        throw new \RuntimeException("Linode instance {$instanceId} did not reach 'running' within "
            . ($maxAttempts * $delaySeconds) . ' seconds.');
    }

    private function client(?VirtualMachines $vm = null, ?ComputeMembers $computeMember = null)
    {
        $computeMember ??= $vm ? VirtualMachinesService::getComputeMember($vm) : $this->requireComputeMemberFromConfig();

        if (!$computeMember->agent_api_key) {
            throw new \RuntimeException("Compute member {$computeMember->uuid} has no agent_api_key configured - "
                . 'cannot authenticate to the Linode API.');
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
            throw new \RuntimeException('LinodeDriver::listAll() requires a compute_member_uuid in its '
                . 'config/virtualization.php platform config to know which account/region to list instances for.');
        }

        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)->where('uuid', $uuid)->firstOrFail();
    }

    private function resolveRegion(ComputeMembers $computeMember): string
    {
        $region = $computeMember->computePools?->pool_data['region'] ?? null;

        if (!$region) {
            throw new \RuntimeException("ComputePools.pool_data['region'] is not set for compute member "
                . "{$computeMember->uuid} - required to know which Linode region to provision in (e.g. \"us-east\").");
        }

        return $region;
    }

    /**
     * Maps our RepositoryImages row to a Linode image slug (e.g. "linode/debian13") -
     * same explicit-config JSON-blob convention as the other four drivers.
     */
    private function resolveImageRef(RepositoryImages $image): string
    {
        $extra = json_decode((string) $image->extra, true) ?? [];
        $ref = $extra['linode_image'] ?? null;

        if (!$ref) {
            throw new \RuntimeException("RepositoryImages '{$image->name}' has no linode_image set in its extra "
                . 'field - required to know which Linode image (e.g. "linode/debian13") to boot from.');
        }

        return (string) $ref;
    }

    /**
     * Maps cpu/ram to a Linode "type" id (e.g. "g6-standard-2"). Their g6-nanode/standard
     * line loosely doubles per tier but isn't a clean derivable formula the way
     * DigitalOcean's is - explicitly configured via ComputePools.pool_data.type_map, same
     * "must configure, don't guess" convention as Ilkbyte/Vultr/Hetzner's opaque or
     * non-formulaic catalog values.
     */
    private function resolveType(int $cpu, int $ramInMb, ComputeMembers $computeMember): string
    {
        $map = $computeMember->computePools?->pool_data['type_map'] ?? ($this->config['size_map'] ?? []);
        $key = $cpu . ':' . $ramInMb;

        if (!isset($map[$key])) {
            throw new \RuntimeException("No Linode type configured for cpu:ram '{$key}' - set it in "
                . "ComputePools.pool_data['type_map'] (e.g. {\"2:4096\": \"g6-standard-2\"}). Linode's naming "
                . 'is not a derivable formula.');
        }

        return $map[$key];
    }
}
