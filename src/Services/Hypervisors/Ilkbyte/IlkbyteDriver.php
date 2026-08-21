<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\Ilkbyte;

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
 * Ilkbyte driver - real API integration against https://apidocs.ilkbyte.com (v2), the
 * second external-provider backend in the multi-cloud aggregator plan (own infra +
 * DigitalOcean + Ilkbyte in one panel). Mirrors DigitalOceanDriver's shape (same
 * capability-interface set, same synthetic-ComputeMembers seeding assumption) but the
 * two providers' actual wire formats have almost nothing in common - see the per-method
 * notes below for what's confirmed from the docs vs. genuinely unconfirmed.
 *
 * Confirmed from https://apidocs.ilkbyte.com/docs/2.0/{overview,server/create,server/manage}:
 * - Auth is two query-string params (access + secret), not a bearer token - see client().
 * - A server is identified by its `name` (3-16 alphanumeric chars), not a numeric/uuid id
 *   assigned by the provider - hypervisor_uuid stores the sanitized name we sent.
 * - Every response is {success, message, data}.
 * - Creation is async: the create call returns a provisioning-state `status`
 *   (InProgress/Active); /show returns a *different* `status` field meaning power state
 *   (e.g. "running") plus a `service` field for provisioning state - two endpoints reuse
 *   the same field name for different things, see waitUntilActive().
 * - No resize/upgrade-package endpoint exists at all (confirmed absent from the manage
 *   page, not just undocumented) - see resize() below for how this driver copes with
 *   Commit.php requiring ResizeCapableInterface unconditionally on every commit.
 * - No console endpoint found in the fetched docs - ConsoleCapableInterface not
 *   implemented, same as DigitalOceanDriver.
 * - Delete requires an exact Turkish confirmation phrase with the server name
 *   interpolated - see delete().
 *
 * Genuinely unconfirmed (only seen in one example response each, not a spec): the full
 * enum of /show's power-state `status` values (only "running" was shown - halted/other
 * states are inferred as "anything else"), and the full enum of provisioning-state
 * `service`/`status` values (only "Active"/"InProgress" mentioned in prose). Both should
 * be verified against a live account before this driver is trusted beyond Phase A testing.
 */
class IlkbyteDriver implements
    VirtualMachineAdapterInterface,
    ProvisioningCapableInterface,
    ResizeCapableInterface,
    ConfigurationIsoCapableInterface,
    HostSyncInterface
{
    private const API_BASE_URL_DEFAULT = 'https://api.ilkbyte.com';

    public function __construct(private readonly array $config = [])
    {
    }

    //  ----- VirtualMachineAdapterInterface -----

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $this->power($vm, 'start');

        return $vm;
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        //  No separate force/graceful distinction documented - "shutdown" is the only
        //  power-off action, used for both cases.
        $this->power($vm, 'shutdown');

        return $vm;
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $this->power($vm, 'reboot');

        return $vm;
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Ilkbyte servers have no pause concept - only start/shutdown/reboot/destroy.');
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Ilkbyte servers have no resume concept - only start/shutdown/reboot/destroy.');
    }

    public function suspend(VirtualMachines $vm): VirtualMachines
    {
        throw new \RuntimeException('Ilkbyte servers have no suspend concept - only start/shutdown/reboot/destroy.');
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        $data = $this->show($vm);

        return [
            'uuid' => $vm->hypervisor_uuid,
            'power-state' => ($data['status'] ?? null) === 'running' ? 'running' : 'halted',
            'raw' => $data,
        ];
    }

    public function delete(VirtualMachines $vm): bool
    {
        //  Confirmed exact literal phrase (Turkish) from apidocs.ilkbyte.com/docs/2.0/server/manage -
        //  not our wording, copy verbatim including the {sunucuadı}->name substitution.
        $confirmation = "Silme işlemi yapacağım {$vm->hypervisor_uuid} isimli sunucunun silinmesinden "
            . 'kaynaklı tüm sorumluluk şahsıma aittir.';

        $response = $this->client($vm)->get("/v2/server/manage/{$vm->hypervisor_uuid}/delete", [
            'text' => $confirmation,
        ])->throw()->json();

        return (bool) ($response['success'] ?? false);
    }

    public function sync(VirtualMachines $vm): VirtualMachines
    {
        $data = $this->show($vm);

        $vm->update([
            'hypervisor_data' => $data,
            'status' => ($data['status'] ?? null) === 'running' ? 'running' : 'halted',
        ]);

        return $vm->fresh();
    }

    public function listAll(): array
    {
        //  No "list all servers" endpoint was found in the fetched docs (only the
        //  per-server /show endpoint, which needs a name already in hand) - unlike
        //  DigitalOceanDriver::listAll(), there's no known endpoint to wrap yet.
        throw new \RuntimeException('IlkbyteDriver::listAll() has no known API endpoint to call - '
            . 'a "list servers" endpoint was not found in the fetched Ilkbyte API docs.');
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
        $name = $this->resolveServerName($vm);
        [$osId, $appId] = $this->resolveOsAndAppId($image);
        $packageId = $this->resolvePackageId($vm, $computeMember);

        $userData = VirtualMachinesMetadataService::getCloudInitConfiguration($vm);

        $params = [
            'name' => $name,
            'os_id' => $osId,
            'app_id' => $appId,
            'package_id' => $packageId,
            'cloud_init' => base64_encode($userData),
            'use_public_ip' => true,
        ];

        $response = $this->client($vm, $computeMember)
            ->get('/v2/server/create/config', $params)
            ->throw()
            ->json();

        if (!($response['success'] ?? false)) {
            throw new \RuntimeException('Ilkbyte server creation failed: ' . ($response['message'] ?? 'unknown error'));
        }

        //  The create response itself already carries the server's identifying data, but
        //  it's not necessarily fully provisioned yet - poll /show until ready, same
        //  reasoning as DigitalOceanDriver::waitUntilActive().
        $vm->update(['hypervisor_uuid' => $name]);

        $data = $this->waitUntilActive($vm, $computeMember, $name);

        $vm->update(['hypervisor_data' => $data]);

        return $name;
    }

    public function getVmParametersByRef(ComputeMembers $computeMember, string $ref): array
    {
        $response = $this->client(null, $computeMember)->get("/v2/server/manage/{$ref}/show")->throw()->json();
        $data = $response['data'] ?? [];

        return [
            'uuid' => $ref,
            'power-state' => ($data['status'] ?? null) === 'running' ? 'running' : 'halted',
        ] + $data;
    }

    public function renameVirtualMachine(VirtualMachines $vm): bool
    {
        //  No rename endpoint found in the fetched docs - the server's identifying name
        //  was already fixed at create time (resolveServerName() below), so unlike
        //  XenServer/DigitalOcean there is nothing to rename to match our internal uuid.
        return true;
    }

    public function injectGuestMetadata(VirtualMachines $vm, string $key, string $value): bool
    {
        //  Same reasoning as DigitalOceanDriver - no live post-boot metadata channel,
        //  cloud_init is create-time-only and already handled in importFromImage().
        return true;
    }

    public function reconcileDiskConfiguration(VirtualMachines $vm): void
    {
        //  Boot disk size is implied by the selected package, not independently
        //  configurable - mark the primary VirtualDiskImages row synced with whatever
        //  size is already on the DB row (nothing authoritative comes back from Ilkbyte's
        //  API about disk size specifically in the show response). Extra volumes: no
        //  volume/disk-attach endpoint was found in the fetched docs at all, unlike
        //  DigitalOcean's Volumes API - left as draft and logged, not Phase B scope yet,
        //  genuinely unresearched.
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

            Log::warning('[IlkbyteDriver] Extra disk row for VM ' . $vm->uuid . ' left as draft - '
                . 'no additional-volume endpoint has been located in the Ilkbyte API docs yet.');
        }
    }

    public function reconcileNetworkConfiguration(VirtualMachines $vm): void
    {
        $data = $vm->hypervisor_data ?? [];

        $cards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($cards as $index => $card) {
            if ($index === 0) {
                $card->update([
                    'device_number' => 0,
                    'hypervisor_uuid' => (string) $vm->hypervisor_uuid,
                    'hypervisor_data' => $data,
                    'status' => 'active',
                    'is_draft' => false,
                ]);

                continue;
            }

            Log::warning('[IlkbyteDriver] Extra network card row for VM ' . $vm->uuid . ' left as draft - '
                . 'multi-NIC support is unresearched for Ilkbyte.');
        }
    }

    //  ----- ResizeCapableInterface -----

    public function resize(VirtualMachines $vm, int $cpu, int $ramInMb, ?int $corePerSocket = null): VirtualMachines
    {
        //  No resize/upgrade-package endpoint exists (confirmed absent from the manage
        //  page docs) - but Commit.php::setCpuRam() calls this unconditionally on every
        //  commit, including the very first one right after import, where cpu/ram already
        //  match what was just provisioned via package_id. Only throw when a real change
        //  is being requested against an already-imported VM; no-op when it's a match.
        if (!$vm->hypervisor_uuid || ((int) $vm->cpu === $cpu && (int) $vm->ram === $ramInMb)) {
            return $vm;
        }

        throw new \RuntimeException('Ilkbyte has no resize/upgrade-package API - changing cpu/ram on an '
            . 'existing server is not supported by this driver. A rebuild onto a different package_id would '
            . 'be destructive and is not implemented.');
    }

    //  ----- ConfigurationIsoCapableInterface -----

    public function regenerateConfigurationIso(VirtualMachines $vm): bool
    {
        //  Same reasoning as DigitalOceanDriver - cloud_init is create-time-only, this
        //  exists purely so GenerateCloudInitImage resolves this driver instead of
        //  falling back to its XenServer-only default.
        return true;
    }

    //  ----- HostSyncInterface -----
    //  No real host behind a synthetic Ilkbyte ComputeMembers row - see
    //  DigitalOceanDriver's HostSyncInterface section for the full reasoning, identical here.

    public function detectVersion(ComputeMembers $computeMember): string
    {
        return 'ilkbyte-api';
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

    private function power(VirtualMachines $vm, string $set): array
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $response = $this->client($vm, $computeMember)
            ->get("/v2/server/manage/{$vm->hypervisor_uuid}/power", ['set' => $set])
            ->throw()
            ->json();

        if (!($response['success'] ?? false)) {
            throw new \RuntimeException("Ilkbyte power action '{$set}' failed for server "
                . "{$vm->hypervisor_uuid}: " . ($response['message'] ?? 'unknown error'));
        }

        return $response['data'] ?? [];
    }

    private function show(VirtualMachines $vm): array
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $response = $this->client($vm, $computeMember)
            ->get("/v2/server/manage/{$vm->hypervisor_uuid}/show")
            ->throw()
            ->json();

        return $response['data'] ?? [];
    }

    private function waitUntilActive(VirtualMachines $vm, ComputeMembers $computeMember, string $name): array
    {
        $maxAttempts = 30;
        $delaySeconds = 5;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $response = $this->client(null, $computeMember)->get("/v2/server/manage/{$name}/show")->throw()->json();
            $data = $response['data'] ?? [];

            //  See class docblock: /show's "service" field is presumed to be the
            //  provisioning-state field (Active/InProgress), distinct from "status"
            //  (power state) - not confirmed against a live account.
            if (($data['service'] ?? null) === 'Active') {
                return $data;
            }

            sleep($delaySeconds);
        }

        throw new \RuntimeException("Ilkbyte server {$name} did not become Active within "
            . ($maxAttempts * $delaySeconds) . ' seconds.');
    }

    private function client(?VirtualMachines $vm = null, ?ComputeMembers $computeMember = null)
    {
        $computeMember ??= $vm ? VirtualMachinesService::getComputeMember($vm) : null;

        if (!$computeMember || !$computeMember->agent_api_key) {
            throw new \RuntimeException('No compute member with an agent_api_key configured - cannot '
                . 'authenticate to the Ilkbyte API.');
        }

        $credentials = json_decode(decrypt($computeMember->agent_api_key), true) ?? [];

        if (!isset($credentials['access'], $credentials['secret'])) {
            throw new \RuntimeException("Compute member {$computeMember->uuid}'s agent_api_key does not contain "
                . "both 'access' and 'secret' keys - Ilkbyte auth requires both.");
        }

        //  Ilkbyte authenticates via query-string params (access/secret), not a bearer
        //  header - very different from DigitalOceanDriver's withToken().
        return Http::baseUrl($this->config['api_base_url'] ?? self::API_BASE_URL_DEFAULT)
            ->withQueryParameters(['access' => $credentials['access'], 'secret' => $credentials['secret']])
            ->acceptJson();
    }

    /**
     * Ilkbyte server names are constrained to 3-16 alphanumeric characters - sanitizes
     * our VM's hostname/uuid down to something that fits, since our own naming
     * conventions (uuid, dotted hostnames) don't satisfy this on their own.
     */
    private function resolveServerName(VirtualMachines $vm): string
    {
        $base = preg_replace('/[^a-zA-Z0-9]/', '', $vm->hostname ?: $vm->name ?: $vm->uuid);
        $name = substr($base, 0, 16);

        if (strlen($name) < 3) {
            throw new \RuntimeException("VM {$vm->uuid}'s hostname/name doesn't yield a valid Ilkbyte server "
                . 'name (3-16 alphanumeric characters required).');
        }

        return $name;
    }

    /**
     * Maps our RepositoryImages row to Ilkbyte's os_id/app_id pair. Both are opaque
     * numeric catalog ids from Ilkbyte's own account (fetched via GET /v2/server/create),
     * not derivable from a naming convention the way DigitalOcean's image slugs loosely
     * are - expects RepositoryImages.extra to carry both, same JSON-blob convention as
     * DigitalOceanDriver::resolveImageSlug().
     */
    private function resolveOsAndAppId(RepositoryImages $image): array
    {
        $extra = json_decode((string) $image->extra, true) ?? [];
        $osId = $extra['ilkbyte_os_id'] ?? null;
        $appId = $extra['ilkbyte_app_id'] ?? null;

        if ($osId === null || $appId === null) {
            throw new \RuntimeException("RepositoryImages '{$image->name}' has no ilkbyte_os_id/ilkbyte_app_id "
                . "set in its extra field - required to know which Ilkbyte OS/app to boot from (one of the "
                . "two is 0 depending on whether this is a plain OS or a pre-built app image).");
        }

        return [(int) $osId, (int) $appId];
    }

    /**
     * Maps cpu/ram to Ilkbyte's package_id (their fixed named plan tiers) - unlike
     * DigitalOcean there is no slug-naming convention to derive this from, package ids
     * are opaque and account-specific (fetched via GET /v2/server/create's package[]
     * list), so this MUST be explicitly configured per compute pool rather than guessed.
     */
    private function resolvePackageId(VirtualMachines $vm, ComputeMembers $computeMember): int
    {
        $packageMap = $computeMember->computePools?->pool_data['package_map'] ?? [];
        $key = $vm->cpu . ':' . $vm->ram;

        if (!isset($packageMap[$key])) {
            throw new \RuntimeException("ComputePools.pool_data['package_map'] has no entry for cpu:ram "
                . "'{$key}' - Ilkbyte package ids are opaque and account-specific, this must be configured "
                . 'explicitly per compute pool, not guessed.');
        }

        return (int) $packageMap[$key];
    }
}
