<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2\XenServer_8_2;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\HypervisorsV2\MigrationInterface;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\AbstractXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class MigrationService implements MigrationInterface
{
    // Known XenServer power states considered valid for migration
    private const KNOWN_POWER_STATES = ['halted', 'running', 'paused', 'suspended'];

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * STEP 1 — Verify SSH connectivity to both hosts, confirm the source VM exists
     * and is in a known state, confirm the target SR exists with sufficient free space,
     * and confirm NFS mounts are accessible on both sides.
     *
     * @throws \Exception on any pre-flight failure
     */
    public function preFlightChecks(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'pre-flight-checks', 0, 'Starting pre-flight checks');

        // ── Resolve models ────────────────────────────────────────────────────
        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        $targetStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_storage_volume_id)
            ->firstOrFail();

        // ── CHECK 1: SSH connectivity to source ───────────────────────────────
        $this->updateStep($migration, 'pre-flight-checks', 2, 'Checking SSH connectivity to source host: ' . $source->name);

        try {
            $result = self::performCommand('echo ok', $source);

            if (trim($result['output'] ?? '') !== 'ok') {
                throw new \Exception('Unexpected response from source host SSH test: ' . $result['output']);
            }
        } catch (CannotConnectWithSshException $e) {
            throw new \Exception('Cannot connect to source host "' . $source->name . '" via SSH: ' . $e->getMessage());
        }

        Log::info(__METHOD__ . ' | SSH OK: ' . $source->name);

        // ── CHECK 2: SSH connectivity to target ───────────────────────────────
        $this->updateStep($migration, 'pre-flight-checks', 4, 'Checking SSH connectivity to target host: ' . $target->name);

        try {
            $result = self::performCommand('echo ok', $target);

            if (trim($result['output'] ?? '') !== 'ok') {
                throw new \Exception('Unexpected response from target host SSH test: ' . $result['output']);
            }
        } catch (CannotConnectWithSshException $e) {
            throw new \Exception('Cannot connect to target host "' . $target->name . '" via SSH: ' . $e->getMessage());
        }

        Log::info(__METHOD__ . ' | SSH OK: ' . $target->name);

        // ── CHECK 3: Source VM exists on hypervisor and has a known power state ─
        $this->updateStep($migration, 'pre-flight-checks', 6, 'Verifying source VM exists on hypervisor');

        $result = self::performCommand(
            'xe vm-param-get uuid=' . $vm->hypervisor_uuid . ' param-name=power-state',
            $source
        );

        if (!empty($result['error']) && str_contains($result['error'], 'uuid')) {
            throw new \Exception(
                'Source VM "' . $vm->name . '" (uuid: ' . $vm->hypervisor_uuid . ') not found on host "' . $source->name . '".'
            );
        }

        $powerState = trim($result['output'] ?? '');

        if (!in_array($powerState, self::KNOWN_POWER_STATES, true)) {
            throw new \Exception(
                'Source VM "' . $vm->name . '" is in unknown power state: "' . $powerState . '". '
                . 'Expected one of: ' . implode(', ', self::KNOWN_POWER_STATES) . '.'
            );
        }

        Log::info(__METHOD__ . ' | VM "' . $vm->name . '" power-state: ' . $powerState);

        // ── CHECK 4: Target SR exists and has enough free space ───────────────
        $this->updateStep($migration, 'pre-flight-checks', 8, 'Verifying target SR and free space');

        $result = self::performCommand(
            'xe sr-param-get uuid=' . $targetStorageVolume->hypervisor_uuid . ' param-name=physical-size',
            $target
        );

        if (!empty($result['error'])) {
            throw new \Exception(
                'Target SR "' . $targetStorageVolume->name . '" (uuid: ' . $targetStorageVolume->hypervisor_uuid . ') '
                . 'not found on host "' . $target->name . '": ' . $result['error']
            );
        }

        $srPhysicalSize = (int) trim($result['output']);

        $result = self::performCommand(
            'xe sr-param-get uuid=' . $targetStorageVolume->hypervisor_uuid . ' param-name=physical-utilisation',
            $target
        );

        $srUsed     = (int) trim($result['output']);
        $srFreeBytes = $srPhysicalSize - $srUsed;

        // Total disk size is stored in the plan options (bytes)
        $plan          = is_array($migration->options) ? $migration->options : json_decode($migration->options, true);
        $totalDiskSize = (int) ($plan['total_disk_size'] ?? 0);

        if ($totalDiskSize > 0 && $srFreeBytes < $totalDiskSize) {
            throw new \Exception(
                'Target SR "' . $targetStorageVolume->name . '" does not have enough free space. '
                . 'Required: ' . $this->formatBytes($totalDiskSize) . ', '
                . 'Available: ' . $this->formatBytes($srFreeBytes) . '.'
            );
        }

        Log::info(__METHOD__ . ' | Target SR free: ' . $this->formatBytes($srFreeBytes)
            . ', required: ' . $this->formatBytes($totalDiskSize));

        // ── CHECK 5: NFS mount accessible on source ───────────────────────────
        $this->updateStep($migration, 'pre-flight-checks', 9, 'Verifying NFS mount on source host');

        $sourceStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_storage_volume_id)
            ->firstOrFail();

        $sourceMountPath = '/var/run/sr-mount/' . $sourceStorageVolume->hypervisor_uuid;

        $result = self::performCommand('ls ' . escapeshellarg($sourceMountPath) . ' > /dev/null 2>&1 && echo ok || echo fail', $source);

        if (trim($result['output']) !== 'ok') {
            throw new \Exception('Source NFS mount not accessible at "' . $sourceMountPath . '" on host "' . $source->name . '".');
        }

        // ── CHECK 6: NFS mount accessible on target ───────────────────────────
        $this->updateStep($migration, 'pre-flight-checks', 10, 'Verifying NFS mount on target host');

        $targetMountPath = '/var/run/sr-mount/' . $targetStorageVolume->hypervisor_uuid;

        $result = self::performCommand('ls ' . escapeshellarg($targetMountPath) . ' > /dev/null 2>&1 && echo ok || echo fail', $target);

        if (trim($result['output']) !== 'ok') {
            throw new \Exception('Target NFS mount not accessible at "' . $targetMountPath . '" on host "' . $target->name . '".');
        }

        $this->updateStep($migration, 'pre-flight-checks', 10, 'Pre-flight checks passed');

        Log::info(__METHOD__ . ' | All pre-flight checks passed for migration: ' . $migration->uuid);
    }

    /**
     * STEP 2 — Collect and return all VM metadata from the source host:
     * VM record, vCPU/memory settings, HVM boot params, platform params,
     * all VBDs + VDIs (with VHD paths), all VIFs (with MAC addresses), PV-args.
     *
     * @return array structured metadata for use in later steps
     */
    public function collectSourceMetadata(VirtualMachineMigrations $migration): array
    {
        $this->updateStep($migration, 'collecting-metadata', 10, 'Collecting source VM metadata');

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        // ── Full VM params ────────────────────────────────────────────────────
        $result   = self::performCommand('xe vm-param-list uuid=' . $vm->hypervisor_uuid, $source);
        $vmParams = AbstractXenService::parseResult($result['output']);

        $this->updateStep($migration, 'collecting-metadata', 12, 'Collected VM params');

        // ── VBDs + VDIs ───────────────────────────────────────────────────────
        $result  = self::performCommand('xe vbd-list vm-uuid=' . $vm->hypervisor_uuid, $source);
        $vbdList = AbstractXenService::parseListResult($result['output']);

        $disks = [];

        foreach ($vbdList as $vbd) {
            if (empty($vbd['uuid'])) {
                continue;
            }

            // Skip CDROMs — they don't need to be migrated
            if (strtoupper(trim($vbd['type'] ?? '')) === 'CD') {
                continue;
            }

            $vdiUuid = trim($vbd['vdi-uuid'] ?? '');

            if (empty($vdiUuid) || $vdiUuid === '<not in database>') {
                continue;
            }

            $result    = self::performCommand('xe vdi-param-list uuid=' . $vdiUuid, $source);
            $vdiParams = AbstractXenService::parseResult($result['output']);

            $srUuid  = trim($vdiParams['sr-uuid'] ?? '');
            $vhdPath = '/var/run/sr-mount/' . $srUuid . '/' . $vdiUuid . '.vhd';

            $disks[] = [
                'vbd_uuid'       => trim($vbd['uuid']),
                'vbd_device'     => trim($vbd['device'] ?? ''),
                'vbd_userdevice' => trim($vbd['userdevice'] ?? ''),
                'vbd_bootable'   => trim($vbd['bootable'] ?? 'false'),
                'vbd_mode'       => trim($vbd['mode'] ?? 'RW'),
                'vbd_type'       => trim($vbd['type'] ?? 'Disk'),
                'vdi_uuid'       => $vdiUuid,
                'vdi_name'       => trim($vdiParams['name-label'] ?? ''),
                'vdi_size_bytes' => (int) trim($vdiParams['virtual-size'] ?? '0'),
                'sr_uuid'        => $srUuid,
                'vhd_path'       => $vhdPath,
            ];
        }

        $this->updateStep($migration, 'collecting-metadata', 15,
            'Collected VBD/VDI metadata for ' . count($disks) . ' disk(s)');

        // ── VIFs ──────────────────────────────────────────────────────────────
        $result  = self::performCommand('xe vif-list vm-uuid=' . $vm->hypervisor_uuid, $source);
        $vifList = AbstractXenService::parseListResult($result['output']);

        $nics = [];

        foreach ($vifList as $vif) {
            if (empty($vif['uuid'])) {
                continue;
            }

            $nics[] = [
                'vif_uuid'     => trim($vif['uuid']),
                'device'       => trim($vif['device'] ?? ''),
                'mac'          => trim($vif['MAC'] ?? ''),
                'network_uuid' => trim($vif['network-uuid'] ?? ''),
                'mtu'          => (int) trim($vif['MTU'] ?? '1500'),
            ];
        }

        $this->updateStep($migration, 'collecting-metadata', 18,
            'Collected VIF metadata for ' . count($nics) . ' NIC(s)');

        // ── Assemble structured metadata ──────────────────────────────────────
        $metadata = [
            'vm'    => [
                'uuid'               => trim($vmParams['uuid'] ?? $vm->hypervisor_uuid),
                'name_label'         => trim($vmParams['name-label'] ?? $vm->name),
                'description'        => trim($vmParams['name-description'] ?? ''),
                'vcpus_max'          => (int) trim($vmParams['VCPUs-max'] ?? '1'),
                'vcpus_at_startup'   => (int) trim($vmParams['VCPUs-at-startup'] ?? '1'),
                'memory_static_min'  => (int) trim($vmParams['memory-static-min'] ?? '0'),
                'memory_static_max'  => (int) trim($vmParams['memory-static-max'] ?? '0'),
                'memory_dynamic_min' => (int) trim($vmParams['memory-dynamic-min'] ?? '0'),
                'memory_dynamic_max' => (int) trim($vmParams['memory-dynamic-max'] ?? '0'),
                'hvm_boot_policy'    => trim($vmParams['HVM-boot-policy'] ?? ''),
                'hvm_boot_params'    => trim($vmParams['HVM-boot-params'] ?? ''),
                'pv_args'            => trim($vmParams['PV-args'] ?? ''),
                'platform'           => trim($vmParams['platform'] ?? ''),
                'power_state'        => trim($vmParams['power-state'] ?? ''),
            ],
            'disks' => $disks,
            'nics'  => $nics,
        ];

        // ── Persist into migration options for use in later steps ─────────────
        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $options['source_metadata'] = $metadata;

        $migration->updateQuietly([
            'options' => json_encode($options),
        ]);

        $this->updateStep($migration, 'collecting-metadata', 20, 'Source metadata collected and saved');

        Log::info(__METHOD__ . ' | Metadata collected: '
            . count($disks) . ' disk(s), ' . count($nics) . ' NIC(s)');

        return $metadata;
    }

    /**
     * STEP 3 — Check for snapshots on the source VM. If snapshots exist, either
     * abort or await operator confirmation before proceeding. After snapshot cleanup,
     * trigger SR scan/coalesce and verify the final VHD is a single flat file.
     *
     * @throws \Exception if snapshots exist and cannot be resolved
     */
    public function validateAndCoalesceVhd(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'validating-vhd', 20, 'Checking for snapshots on source VM');

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        // ── CHECK: Snapshots ──────────────────────────────────────────────────
        $result    = self::performCommand('xe snapshot-list snapshot-of=' . $vm->hypervisor_uuid . ' params=uuid', $source);
        $snapshots = AbstractXenService::parseListResult($result['output']);
        $snapshots = array_filter($snapshots, fn($s) => !empty($s['uuid']));

        if (!empty($snapshots)) {
            $snapshotUuids = array_column($snapshots, 'uuid');
            $snapshotUuids = array_map('trim', $snapshotUuids);

            Log::warning(__METHOD__ . ' | VM "' . $vm->name . '" has ' . count($snapshotUuids) . ' snapshot(s): '
                . implode(', ', $snapshotUuids));

            // If operator has not explicitly approved snapshot deletion, pause and await confirmation
            if (empty($options['force_delete_snapshots'])) {
                $options['pending_snapshot_uuids'] = $snapshotUuids;

                $migration->updateQuietly([
                    'status'       => 'awaiting-confirmation',
                    'options'      => json_encode($options),
                    'step_message' => 'VM has ' . count($snapshotUuids) . ' snapshot(s) that must be deleted before migration. '
                        . 'Set options.force_delete_snapshots = true and re-run to proceed.',
                ]);

                throw new \Exception(
                    'VM "' . $vm->name . '" has ' . count($snapshotUuids) . ' snapshot(s). '
                    . 'Set options.force_delete_snapshots = true on the migration record to allow deletion and continue.'
                );
            }

            // Operator confirmed — delete all snapshots
            $this->updateStep($migration, 'validating-vhd', 22,
                'Deleting ' . count($snapshotUuids) . ' snapshot(s) as approved by operator');

            foreach ($snapshotUuids as $snapshotUuid) {
                $result = self::performCommand('xe snapshot-destroy uuid=' . $snapshotUuid, $source);

                if (!empty($result['error'])) {
                    throw new \Exception('Failed to delete snapshot ' . $snapshotUuid . ': ' . $result['error']);
                }

                Log::info(__METHOD__ . ' | Deleted snapshot: ' . $snapshotUuid);
            }

            unset($options['pending_snapshot_uuids']);
        }

        $this->updateStep($migration, 'validating-vhd', 25, 'No blocking snapshots — triggering SR scan for coalesce');

        // ── Trigger SR scan on source SR to allow VHD coalesce ───────────────
        $sourceStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_storage_volume_id)
            ->firstOrFail();

        self::performCommand('xe sr-scan uuid=' . $sourceStorageVolume->hypervisor_uuid, $source);

        // ── Wait for coalesce: poll VHD parent chain until flat ───────────────
        $this->updateStep($migration, 'validating-vhd', 27, 'Waiting for VHD coalesce on source SR');

        $metadata = $options['source_metadata'] ?? null;

        if (empty($metadata['disks'])) {
            throw new \Exception('No disk metadata found. Run collectSourceMetadata before this step.');
        }

        $maxAttempts   = 24; // 24 × 10s = 4 minutes
        $coalescedDisks = [];

        foreach ($metadata['disks'] as $disk) {
            $vhdPath  = $disk['vhd_path'];
            $coalesced = false;

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $result = self::performCommand(
                    'vhd-util query -n ' . escapeshellarg($vhdPath) . ' -p 2>&1',
                    $source
                );

                $output = trim($result['output'] ?? '');

                // vhd-util returns "has no parent" (exit non-zero) when the VHD is flat
                $isFlat = empty($output)
                    || str_contains($output, 'has no parent')
                    || str_contains($output, 'no parent');

                if ($isFlat) {
                    Log::info(__METHOD__ . ' | VHD is flat: ' . $vhdPath);
                    $coalesced = true;
                    break;
                }

                Log::info(__METHOD__ . ' | VHD still has parent chain, waiting... attempt ' . ($attempt + 1));
                sleep(10);

                // Re-trigger SR scan each iteration to nudge coalesce daemon
                self::performCommand('xe sr-scan uuid=' . $sourceStorageVolume->hypervisor_uuid, $source);
            }

            if (!$coalesced) {
                throw new \Exception(
                    'VHD "' . $vhdPath . '" still has a parent chain after ' . ($maxAttempts * 10) . ' seconds. '
                    . 'Coalesce did not complete in time.'
                );
            }

            // Verify the VHD file actually exists at the expected path
            $result = self::performCommand(
                'test -f ' . escapeshellarg($vhdPath) . ' && echo ok || echo missing',
                $source
            );

            if (trim($result['output']) !== 'ok') {
                throw new \Exception('VHD file not found at expected path: ' . $vhdPath);
            }

            $coalescedDisks[] = $vhdPath;
        }

        // ── Persist coalesced VHD paths back into options ─────────────────────
        $options['coalesced_vhd_paths'] = $coalescedDisks;

        $migration->updateQuietly([
            'options' => json_encode($options),
        ]);

        $this->updateStep($migration, 'validating-vhd', 35,
            'VHD validation complete — ' . count($coalescedDisks) . ' flat VHD(s) ready for copy');

        Log::info(__METHOD__ . ' | All VHDs coalesced and verified for migration: ' . $migration->uuid);
    }

    /**
     * STEP 4 — Gracefully shut down the source VM. Poll power-state until halted
     * (timeout: 5 minutes). Falls back to forced shutdown if needed.
     *
     * @throws \Exception if the VM cannot be halted
     */
    public function shutdownSourceVm(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'shutting-down', 35, 'Initiating graceful shutdown of source VM');

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        // ── Check current power state ─────────────────────────────────────────
        $result     = self::performCommand('xe vm-param-get uuid=' . $vm->hypervisor_uuid . ' param-name=power-state', $source);
        $powerState = trim($result['output'] ?? '');

        if ($powerState === 'halted') {
            $this->updateStep($migration, 'shutting-down', 45, 'VM is already halted — skipping shutdown');
            Log::info(__METHOD__ . ' | VM "' . $vm->name . '" is already halted.');
            return;
        }

        // ── Attempt graceful shutdown (timeout: 5 minutes) ───────────────────
        $this->updateStep($migration, 'shutting-down', 37, 'Sending clean shutdown signal to VM: ' . $vm->name);

        self::performCommand('xe vm-shutdown uuid=' . $vm->hypervisor_uuid . ' force=false', $source);

        $halted      = false;
        $maxAttempts = 30; // 30 × 10s = 5 minutes

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            sleep(10);

            $result     = self::performCommand('xe vm-param-get uuid=' . $vm->hypervisor_uuid . ' param-name=power-state', $source);
            $powerState = trim($result['output'] ?? '');

            Log::info(__METHOD__ . ' | Poll ' . ($attempt + 1) . '/' . $maxAttempts . ' — power-state: ' . $powerState);

            if ($powerState === 'halted') {
                $halted = true;
                break;
            }

            $progress = 37 + (int) (($attempt / $maxAttempts) * 6); // 37 → 43
            $this->updateStep($migration, 'shutting-down', $progress,
                'Waiting for VM to halt... (' . (($attempt + 1) * 10) . 's elapsed)');
        }

        // ── Graceful shutdown timed out — attempt forced shutdown ─────────────
        if (!$halted) {
            $this->updateStep($migration, 'shutting-down', 43,
                'Graceful shutdown timed out after 5 minutes — attempting forced shutdown');

            Log::warning(__METHOD__ . ' | Graceful shutdown timed out for VM "' . $vm->name . '". Forcing shutdown.');

            self::performCommand('xe vm-shutdown uuid=' . $vm->hypervisor_uuid . ' force=true', $source);

            // Wait up to 60 more seconds after forced shutdown
            for ($attempt = 0; $attempt < 6; $attempt++) {
                sleep(10);

                $result     = self::performCommand('xe vm-param-get uuid=' . $vm->hypervisor_uuid . ' param-name=power-state', $source);
                $powerState = trim($result['output'] ?? '');

                if ($powerState === 'halted') {
                    $halted = true;
                    break;
                }
            }

            if (!$halted) {
                throw new \Exception(
                    'VM "' . $vm->name . '" could not be halted even after forced shutdown. '
                    . 'Current power-state: "' . $powerState . '". Manual intervention required.'
                );
            }

            Log::info(__METHOD__ . ' | VM "' . $vm->name . '" halted via forced shutdown.');
        }

        // ── Update DB VM record ───────────────────────────────────────────────
        $vm->updateQuietly(['status' => 'halted']);

        $this->updateStep($migration, 'shutting-down', 45, 'VM halted successfully');

        Log::info(__METHOD__ . ' | VM "' . $vm->name . '" is halted. Proceeding with migration.');
    }

    /**
     * STEP 5 — Copy each VDI's VHD file from the source NFS SR to the target NFS SR.
     * Preferred: rsync with --checksum --progress over SSH.
     * Fallback: dd over SSH pipe.
     * Verifies file integrity (size or checksum) after each transfer.
     *
     * @throws \Exception on transfer or integrity failure
     */
    public function copyVhdFiles(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'copying-vhd', 45, 'Preparing VHD copy via storage members');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        if (empty($options['coalesced_vhd_paths'])) {
            throw new \Exception('No coalesced VHD paths found. Run validateAndCoalesceVhd before this step.');
        }

        // ── Resolve storage members ───────────────────────────────────────────
        $sourceStorageMember = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_storage_member_id)
            ->firstOrFail();

        // ── Resolve NFS coordinates from ComputeMemberStorageVolumes ──────────
        // Source: local serverpath where VHDs live on source Ubuntu storage
        $sourceCmVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $migration->source_iaas_compute_member_id)
            ->where('iaas_storage_volume_id', $migration->source_iaas_storage_volume_id)
            ->firstOrFail();

        $sourceServerPath = rtrim($sourceCmVolume->block_device_data['device-config']['serverpath'] ?? '', '/');

        // Target: NFS server IP and export path to mount on source storage
        $targetCmVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $migration->target_iaas_compute_member_id)
            ->where('iaas_storage_volume_id', $migration->target_iaas_storage_volume_id)
            ->firstOrFail();

        $targetNfsServer     = $targetCmVolume->block_device_data['device-config']['server'] ?? null;
        $targetNfsServerPath = rtrim($targetCmVolume->block_device_data['device-config']['serverpath'] ?? '', '/');

        if (!$targetNfsServer || !$targetNfsServerPath) {
            throw new \Exception('Target NFS coordinates missing from block_device_data. Cannot proceed with copy.');
        }

        // ── Create temp mount point on source storage member ──────────────────
        $mountPoint = '/tmp/migration-' . $migration->uuid;

        $this->updateStep($migration, 'copying-vhd', 46, 'Mounting target NFS share on source storage');

        self::performStorageCommand('mkdir -p ' . escapeshellarg($mountPoint), $sourceStorageMember);

        $mountResult = self::performStorageCommand(
            'mount -t nfs ' . escapeshellarg($targetNfsServer . ':' . $targetNfsServerPath)
            . ' ' . escapeshellarg($mountPoint),
            $sourceStorageMember
        );

        if (!empty($mountResult['error']) && !str_contains($mountResult['error'], 'already mounted')) {
            throw new \Exception('Failed to mount target NFS share: ' . $mountResult['error']);
        }

        Log::info(__METHOD__ . ' | Mounted ' . $targetNfsServer . ':' . $targetNfsServerPath . ' at ' . $mountPoint);

        // ── Copy each VHD ─────────────────────────────────────────────────────
        $copiedPaths    = [];
        $vhdPaths       = $options['coalesced_vhd_paths'];
        $total          = count($vhdPaths);
        $progressPerVhd = (int) floor(22 / max($total, 1)); // spread 46 → 68 across all VHDs

        try {
            foreach ($vhdPaths as $index => $vhdPath) {
                $vdiUuid    = basename($vhdPath, '.vhd');
                $sourcePath = $sourceServerPath . '/' . $vdiUuid . '.vhd';
                $targetPath = $mountPoint . '/' . $vdiUuid . '.vhd';

                $progress = 46 + ($index * $progressPerVhd);
                $this->updateStep($migration, 'copying-vhd', $progress,
                    'Copying VHD ' . ($index + 1) . '/' . $total . ': ' . $vdiUuid . '.vhd');

                Log::info(__METHOD__ . ' | Starting rsync: ' . $sourcePath . ' -> ' . $targetPath);

                $rsyncResult = self::performStorageCommand(
                    'rsync -avz --checksum --partial --progress '
                    . escapeshellarg($sourcePath) . ' '
                    . escapeshellarg($targetPath),
                    $sourceStorageMember
                );

                if (!empty($rsyncResult['error']) && !str_contains($rsyncResult['output'], 'sent')) {
                    throw new \Exception('rsync failed for ' . $vdiUuid . '.vhd: ' . $rsyncResult['error']);
                }

                Log::info(__METHOD__ . ' | rsync complete for: ' . $vdiUuid . '.vhd');

                // ── Integrity check: compare file sizes ───────────────────────
                $sourceSizeResult = self::performStorageCommand(
                    'stat -c%s ' . escapeshellarg($sourcePath),
                    $sourceStorageMember
                );
                $targetSizeResult = self::performStorageCommand(
                    'stat -c%s ' . escapeshellarg($targetPath),
                    $sourceStorageMember
                );

                $sourceSize = (int) trim($sourceSizeResult['output']);
                $targetSize = (int) trim($targetSizeResult['output']);

                if ($sourceSize === 0) {
                    throw new \Exception('Source VHD size is 0 for ' . $vdiUuid . '.vhd — source file may be missing.');
                }

                if ($sourceSize !== $targetSize) {
                    throw new \Exception(
                        'Integrity check failed for ' . $vdiUuid . '.vhd: '
                        . 'source=' . $this->formatBytes($sourceSize)
                        . ', target=' . $this->formatBytes($targetSize)
                    );
                }

                Log::info(__METHOD__ . ' | Integrity OK: ' . $vdiUuid . '.vhd (' . $this->formatBytes($sourceSize) . ')');

                $copiedPaths[] = [
                    'vdi_uuid'    => $vdiUuid,
                    'source_path' => $sourcePath,
                    'target_path' => $targetNfsServerPath . '/' . $vdiUuid . '.vhd',
                    'size_bytes'  => $sourceSize,
                ];
            }
        } finally {
            // ── Always unmount, even on failure ──────────────────────────────
            self::performStorageCommand('umount ' . escapeshellarg($mountPoint), $sourceStorageMember);
            self::performStorageCommand('rmdir ' . escapeshellarg($mountPoint), $sourceStorageMember);

            Log::info(__METHOD__ . ' | Unmounted and cleaned up: ' . $mountPoint);
        }

        // ── Persist copied paths into migration options ───────────────────────
        $options['copied_vhd_paths'] = $copiedPaths;

        $migration->updateQuietly([
            'options' => json_encode($options),
        ]);

        $this->updateStep($migration, 'copying-vhd', 70,
            'All ' . count($copiedPaths) . ' VHD(s) copied and verified');

        Log::info(__METHOD__ . ' | VHD copy complete for migration: ' . $migration->uuid);
    }

    /**
     * STEP 6 — Trigger `xe sr-scan` on the target host, then query the SR to find
     * newly detected VDIs. Returns a map of original VDI UUID => new VDI UUID on target.
     *
     * @return array<string, string> [ source_vdi_uuid => target_vdi_uuid ]
     */
    public function rescanTargetSr(VirtualMachineMigrations $migration): array
    {
        $this->updateStep($migration, 'rescanning-sr', 70, 'Triggering SR scan on target host');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        if (empty($options['copied_vhd_paths'])) {
            throw new \Exception('No copied VHD paths found. Run copyVhdFiles before this step.');
        }

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $targetStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_storage_volume_id)
            ->firstOrFail();

        $targetSrUuid = $targetStorageVolume->hypervisor_uuid;

        // ── Trigger SR scan ───────────────────────────────────────────────────
        self::performCommand('xe sr-scan uuid=' . $targetSrUuid, $target);

        // Give the scan a moment to register all VDIs before querying
        sleep(5);

        $this->updateStep($migration, 'rescanning-sr', 73, 'Querying VDI list on target SR');

        // ── Query all VDIs now present in the target SR ───────────────────────
        $result  = self::performCommand('xe vdi-list sr-uuid=' . $targetSrUuid . ' params=uuid,name-label,virtual-size', $target);
        $vdiList = AbstractXenService::parseListResult($result['output']);

        // Index target VDIs by UUID for fast lookup
        $targetVdisByUuid = [];

        foreach ($vdiList as $vdi) {
            $uuid = trim($vdi['uuid'] ?? '');

            if (!empty($uuid)) {
                $targetVdisByUuid[$uuid] = $vdi;
            }
        }

        Log::info(__METHOD__ . ' | Found ' . count($targetVdisByUuid) . ' VDI(s) in target SR after scan');

        // ── Match each copied VHD to its VDI on the target ───────────────────
        // VHD files preserve their VDI UUID inside the file metadata, so xe sr-scan
        // registers them with the same UUID as on the source.
        $vdiUuidMap  = [];
        $unmatched   = [];

        foreach ($options['copied_vhd_paths'] as $copied) {
            $sourceVdiUuid = $copied['vdi_uuid'];

            if (isset($targetVdisByUuid[$sourceVdiUuid])) {
                $vdiUuidMap[$sourceVdiUuid] = $sourceVdiUuid;

                Log::info(__METHOD__ . ' | Matched VDI: ' . $sourceVdiUuid);
            } else {
                $unmatched[] = $sourceVdiUuid;

                Log::warning(__METHOD__ . ' | VDI not found in target SR after scan: ' . $sourceVdiUuid);
            }
        }

        if (!empty($unmatched)) {
            throw new \Exception(
                'The following VDI(s) were not detected in the target SR after scan: '
                . implode(', ', $unmatched) . '. '
                . 'Verify the VHD files were copied correctly and re-run this step.'
            );
        }

        // ── Persist the map for use in recreateVmOnTarget ────────────────────
        $options['vdi_uuid_map'] = $vdiUuidMap;

        $migration->updateQuietly([
            'options' => json_encode($options),
        ]);

        $this->updateStep($migration, 'rescanning-sr', 80,
            'SR scan complete — ' . count($vdiUuidMap) . ' VDI(s) confirmed on target');

        Log::info(__METHOD__ . ' | VDI map: ' . json_encode($vdiUuidMap));

        return $vdiUuidMap;
    }

    /**
     * STEP 7 — Recreate the VM record on the target host using the metadata collected
     * in Step 2: VM record, vCPU/memory params, HVM/platform params, VBDs (with the
     * new VDI UUIDs from Step 6), and VIFs (using operator-supplied network UUID mapping,
     * preserving original MAC addresses). VM is NOT started.
     *
     * @return string the new VM UUID on the target host
     */
    public function recreateVmOnTarget(VirtualMachineMigrations $migration, array $vdiUuidMap): string
    {
        $this->updateStep($migration, 'recreating-vm', 80, 'Recreating VM record on target host');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $metadata = $options['source_metadata'] ?? null;

        if (empty($metadata)) {
            throw new \Exception('No source metadata found. Run collectSourceMetadata before this step.');
        }

        // Use the passed map or fall back to what was persisted in options
        if (empty($vdiUuidMap)) {
            $vdiUuidMap = $options['vdi_uuid_map'] ?? [];
        }

        if (empty($vdiUuidMap)) {
            throw new \Exception('No VDI UUID map found. Run rescanTargetSr before this step.');
        }

        $target  = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $vmMeta = $metadata['vm'];

        // ── 1. Create VM skeleton ─────────────────────────────────────────────
        $result = self::performCommand(
            'xe vm-create name-label=' . escapeshellarg($vmMeta['name_label'])
            . ' name-description=' . escapeshellarg($vmMeta['description']),
            $target
        );

        $newVmUuid = trim($result['output']);

        if (empty($newVmUuid)) {
            throw new \Exception('Failed to create VM on target host. xe vm-create returned empty UUID.');
        }

        $this->updateStep($migration, 'recreating-vm', 82, 'Created VM skeleton: ' . $newVmUuid);
        Log::info(__METHOD__ . ' | Created VM: ' . $newVmUuid);

        // ── 2. Set vCPU params ────────────────────────────────────────────────
        self::performCommand(
            'xe vm-param-set uuid=' . $newVmUuid
            . ' VCPUs-max=' . (int) $vmMeta['vcpus_max']
            . ' VCPUs-at-startup=' . (int) $vmMeta['vcpus_at_startup'],
            $target
        );

        // ── 3. Set memory params ──────────────────────────────────────────────
        self::performCommand(
            'xe vm-param-set uuid=' . $newVmUuid
            . ' memory-static-min=' . (int) $vmMeta['memory_static_min']
            . ' memory-static-max=' . (int) $vmMeta['memory_static_max']
            . ' memory-dynamic-min=' . (int) $vmMeta['memory_dynamic_min']
            . ' memory-dynamic-max=' . (int) $vmMeta['memory_dynamic_max'],
            $target
        );

        $this->updateStep($migration, 'recreating-vm', 84, 'Set CPU and memory parameters');

        // ── 4. Set HVM boot policy and params ─────────────────────────────────
        if (!empty($vmMeta['hvm_boot_policy'])) {
            self::performCommand(
                'xe vm-param-set uuid=' . $newVmUuid
                . ' HVM-boot-policy=' . escapeshellarg($vmMeta['hvm_boot_policy']),
                $target
            );
        }

        // hvm_boot_params stored as "key: value; key: value" — parse and set each
        if (!empty($vmMeta['hvm_boot_params'])) {
            foreach (self::parseMapString($vmMeta['hvm_boot_params']) as $key => $value) {
                self::performCommand(
                    'xe vm-param-set uuid=' . $newVmUuid
                    . ' HVM-boot-params:' . $key . '=' . escapeshellarg($value),
                    $target
                );
            }
        }

        // ── 5. Set platform params ────────────────────────────────────────────
        if (!empty($vmMeta['platform'])) {
            foreach (self::parseMapString($vmMeta['platform']) as $key => $value) {
                self::performCommand(
                    'xe vm-param-set uuid=' . $newVmUuid
                    . ' platform:' . $key . '=' . escapeshellarg($value),
                    $target
                );
            }
        }

        // ── 6. Set PV args if present (PV-boot VMs) ───────────────────────────
        if (!empty($vmMeta['pv_args'])) {
            self::performCommand(
                'xe vm-param-set uuid=' . $newVmUuid . ' PV-args=' . escapeshellarg($vmMeta['pv_args']),
                $target
            );
        }

        $this->updateStep($migration, 'recreating-vm', 86, 'Set boot and platform parameters');

        // ── 7. Create VBDs (attach disks) ─────────────────────────────────────
        foreach ($metadata['disks'] as $disk) {
            $sourceVdiUuid = $disk['vdi_uuid'];
            $targetVdiUuid = $vdiUuidMap[$sourceVdiUuid] ?? null;

            if (!$targetVdiUuid) {
                throw new \Exception(
                    'No target VDI UUID mapping found for source VDI: ' . $sourceVdiUuid
                );
            }

            $bootable = (($disk['vbd_bootable'] ?? 'false') === 'true') ? 'true' : 'false';

            $result = self::performCommand(
                'xe vbd-create'
                . ' vm-uuid=' . $newVmUuid
                . ' vdi-uuid=' . $targetVdiUuid
                . ' device=' . escapeshellarg($disk['vbd_userdevice'])
                . ' bootable=' . $bootable
                . ' mode=' . escapeshellarg(strtoupper($disk['vbd_mode'] ?? 'RW'))
                . ' type=' . escapeshellarg($disk['vbd_type'] ?? 'Disk'),
                $target
            );

            if (!empty($result['error'])) {
                throw new \Exception(
                    'Failed to create VBD for VDI ' . $targetVdiUuid . ': ' . $result['error']
                );
            }

            Log::info(__METHOD__ . ' | Created VBD for VDI: ' . $targetVdiUuid
                . ' (device=' . $disk['vbd_userdevice'] . ', bootable=' . $bootable . ')');
        }

        $this->updateStep($migration, 'recreating-vm', 90,
            'Created ' . count($metadata['disks']) . ' VBD(s)');

        // ── 8. Build source hypervisor network UUID → target XenServer network UUID map ──
        //
        // ComputeMemberNetworkInterfaces stores per-host network UUIDs (network_uuid).
        // If the network does not yet exist on the target host, we create it automatically
        // using ComputeMemberXenService::createNetwork(), which also handles xe network-create
        // + xe vlan-create and syncs the result back into ComputeMemberNetworkInterfaces.
        $networkMapping            = $options['network_mapping'] ?? [];
        $targetNetworkUuidBySource = []; // source hypervisor_uuid (network) → target XenServer network_uuid

        foreach ($networkMapping as $map) {
            $sourceNetworkId = $map['source_network']['id'] ?? null;

            if (!$sourceNetworkId) {
                continue;
            }

            $sourceNetwork = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $sourceNetworkId)
                ->first();

            if (!$sourceNetwork) {
                continue;
            }

            // Prefer the plan-mapped target network; fall back to source network (same VLAN/name)
            $targetNetworkId = $map['target_network']['id'] ?? null;
            $networkForCreate = $targetNetworkId
                ? Networks::withoutGlobalScope(AuthorizationScope::class)->where('id', $targetNetworkId)->first()
                : $sourceNetwork;

            if (!$networkForCreate) {
                $networkForCreate = $sourceNetwork;
            }

            // Check if the target host already has this VLAN in its ComputeMemberNetworkInterfaces
            $cmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $target->id)
                ->where('vlan', $networkForCreate->vlan)
                ->first();

            if (!$cmni) {
                // Network does not exist on the target host — create it now
                Log::info(__METHOD__ . ' | Network VLAN ' . $networkForCreate->vlan
                    . ' not found on target "' . $target->name . '" — creating it');

                $cmni = ComputeMemberXenService::createNetwork($target, $networkForCreate);

                Log::info(__METHOD__ . ' | Network created on target: network_uuid=' . $cmni->network_uuid
                    . ', vlan=' . $cmni->vlan);
            }

            $targetNetworkUuidBySource[$sourceNetwork->hypervisor_uuid] = $cmni->network_uuid;
        }

        // ── 9. Create VIFs (attach NICs), preserving MAC addresses ───────────
        foreach ($metadata['nics'] as $nic) {
            $sourceNetworkUuid = $nic['network_uuid'];
            $targetNetworkUuid = $targetNetworkUuidBySource[$sourceNetworkUuid] ?? null;

            if (!$targetNetworkUuid) {
                throw new \Exception(
                    'Could not resolve a target network UUID for source network UUID: ' . $sourceNetworkUuid
                    . '. Verify the evacuation plan has a network mapping for all NICs.'
                );
            }

            $result = self::performCommand(
                'xe vif-create'
                . ' vm-uuid=' . $newVmUuid
                . ' network-uuid=' . $targetNetworkUuid
                . ' device=' . escapeshellarg($nic['device'])
                . ' mac=' . escapeshellarg($nic['mac'])
                . ' mtu=' . (int) ($nic['mtu'] ?? 1500),
                $target
            );

            if (!empty($result['error'])) {
                throw new \Exception(
                    'Failed to create VIF for device ' . $nic['device'] . ': ' . $result['error']
                );
            }

            Log::info(__METHOD__ . ' | Created VIF: device=' . $nic['device'] . ', mac=' . $nic['mac']
                . ', network_uuid=' . $targetNetworkUuid);
        }

        $this->updateStep($migration, 'recreating-vm', 92,
            'Created ' . count($metadata['nics']) . ' VIF(s) — VM recreated on target (not started)');

        // ── Persist new VM UUID into options ──────────────────────────────────
        $options['target_vm_uuid'] = $newVmUuid;

        $migration->updateQuietly([
            'options' => json_encode($options),
        ]);

        Log::info(__METHOD__ . ' | VM recreated on target: ' . $newVmUuid);

        return $newVmUuid;
    }

    /**
     * STEP 8 — Verify the recreated VM record is complete: correct vCPU count,
     * memory settings, all disks and NICs present. Returns a structured validation summary.
     *
     * @return array validation summary
     */
    public function postMigrationValidation(VirtualMachineMigrations $migration): array
    {
        $this->updateStep($migration, 'post-validation', 92, 'Validating recreated VM on target host');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $newVmUuid = $options['target_vm_uuid'] ?? null;

        if (empty($newVmUuid)) {
            throw new \Exception('No target VM UUID found. Run recreateVmOnTarget before this step.');
        }

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $sourceMeta = $options['source_metadata']['vm'] ?? [];

        // ── Verify VM params ──────────────────────────────────────────────────
        $result   = self::performCommand('xe vm-param-list uuid=' . $newVmUuid, $target);
        $vmParams = AbstractXenService::parseResult($result['output']);

        $actualVcpus    = (int) trim($vmParams['VCPUs-max'] ?? '0');
        $actualMemory   = (int) trim($vmParams['memory-static-max'] ?? '0');
        $expectedVcpus  = (int) ($sourceMeta['vcpus_max'] ?? 0);
        $expectedMemory = (int) ($sourceMeta['memory_static_max'] ?? 0);

        // ── Count VBDs ────────────────────────────────────────────────────────
        $result  = self::performCommand('xe vbd-list vm-uuid=' . $newVmUuid . ' type=Disk params=uuid', $target);
        $vbdList = array_filter(AbstractXenService::parseListResult($result['output']), fn($v) => !empty($v['uuid']));

        $actualDiskCount   = count($vbdList);
        $expectedDiskCount = count($options['source_metadata']['disks'] ?? []);

        // ── Count VIFs ────────────────────────────────────────────────────────
        $result  = self::performCommand('xe vif-list vm-uuid=' . $newVmUuid . ' params=uuid', $target);
        $vifList = array_filter(AbstractXenService::parseListResult($result['output']), fn($v) => !empty($v['uuid']));

        $actualNicCount   = count($vifList);
        $expectedNicCount = count($options['source_metadata']['nics'] ?? []);

        $checks = [
            'vcpus'  => ['expected' => $expectedVcpus,    'actual' => $actualVcpus,    'pass' => $actualVcpus === $expectedVcpus],
            'memory' => ['expected' => $expectedMemory,   'actual' => $actualMemory,   'pass' => $actualMemory === $expectedMemory],
            'disks'  => ['expected' => $expectedDiskCount,'actual' => $actualDiskCount,'pass' => $actualDiskCount === $expectedDiskCount],
            'nics'   => ['expected' => $expectedNicCount, 'actual' => $actualNicCount, 'pass' => $actualNicCount === $expectedNicCount],
        ];

        $isValid  = !in_array(false, array_column($checks, 'pass'), true);
        $failures = array_keys(array_filter($checks, fn($c) => !$c['pass']));

        $summary = [
            'is_valid'       => $isValid,
            'target_vm_uuid' => $newVmUuid,
            'checks'         => $checks,
        ];

        if (!$isValid) {
            Log::warning(__METHOD__ . ' | Validation failures: ' . implode(', ', $failures));
        }

        // ── Persist validation summary ────────────────────────────────────────
        $options['post_validation'] = $summary;
        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'post-validation', 95,
            $isValid
                ? 'Validation passed — VM record is complete on target'
                : 'Validation FAILED: ' . implode(', ', $failures));

        Log::info(__METHOD__ . ' | Validation result: ' . ($isValid ? 'PASS' : 'FAIL'));

        return $summary;
    }

    /**
     * STEP 9 — Sync the database records to reflect the completed migration:
     * - Update VirtualMachines: new hypervisor_uuid, target compute member, status = halted
     * - Update each VirtualDiskImages: new hypervisor_uuid (target VDI UUID) and target storage volume
     * - Update each VirtualNetworkCards: target network ID from the plan's network_mapping
     */
    public function syncDatabaseRecords(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'syncing-database', 95, 'Syncing database records to target');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $newVmUuid = $options['target_vm_uuid'] ?? null;

        if (empty($newVmUuid)) {
            throw new \Exception('No target VM UUID found. Run recreateVmOnTarget before this step.');
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        // ── Update VirtualMachines record ─────────────────────────────────────
        $vm->updateQuietly([
            'hypervisor_uuid'        => $newVmUuid,
            'iaas_compute_member_id' => $migration->target_iaas_compute_member_id,
            'status'                 => 'halted',
        ]);

        Log::info(__METHOD__ . ' | Updated VirtualMachine: uuid=' . $newVmUuid
            . ', compute_member_id=' . $migration->target_iaas_compute_member_id);

        // ── Update VirtualDiskImages: new VDI UUID + target storage volume ────
        $storageMapping = $options['storage_mapping'] ?? [];
        $vdiUuidMap     = $options['vdi_uuid_map'] ?? [];

        foreach ($storageMapping as $map) {
            $diskId              = $map['disk']['id'] ?? null;
            $targetStorageVolume = $map['target_storage_volume'] ?? null;

            if (!$diskId || !$targetStorageVolume) {
                continue;
            }

            $disk = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $diskId)
                ->first();

            if (!$disk) {
                Log::warning(__METHOD__ . ' | VirtualDiskImage not found for id=' . $diskId);
                continue;
            }

            $newVdiUuid = $vdiUuidMap[$disk->hypervisor_uuid] ?? null;

            $updates = ['iaas_storage_volume_id' => $targetStorageVolume['id']];

            if ($newVdiUuid) {
                $updates['hypervisor_uuid'] = $newVdiUuid;
            }

            $disk->updateQuietly($updates);

            Log::info(__METHOD__ . ' | Updated VirtualDiskImage id=' . $diskId
                . ' → storage_volume_id=' . $targetStorageVolume['id']
                . ($newVdiUuid ? ', hypervisor_uuid=' . $newVdiUuid : ''));
        }

        // ── Update VirtualNetworkCards: target network ID ─────────────────────
        $networkMapping = $options['network_mapping'] ?? [];

        foreach ($networkMapping as $map) {
            $nicId         = $map['nic']['id'] ?? null;
            $targetNetwork = $map['target_network'] ?? null;

            if (!$nicId || !$targetNetwork) {
                continue;
            }

            $nic = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $nicId)
                ->first();

            if (!$nic) {
                Log::warning(__METHOD__ . ' | VirtualNetworkCard not found for id=' . $nicId);
                continue;
            }

            $nic->updateQuietly(['iaas_network_id' => $targetNetwork['id']]);

            Log::info(__METHOD__ . ' | Updated VirtualNetworkCard id=' . $nicId
                . ' → network_id=' . $targetNetwork['id']);
        }

        $this->updateStep($migration, 'syncing-database', 97, 'Database records synced to target');

        Log::info(__METHOD__ . ' | Database sync complete for migration: ' . $migration->uuid);
    }

    /**
     * STEP 10 — Start the VM on the target host. Poll power-state until running
     * (timeout: 3 minutes). Updates the VM status on success.
     *
     * @throws \Exception if the VM does not reach running state within the timeout
     */
    public function startVmOnTarget(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'starting-vm', 95, 'Starting VM on target host');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $newVmUuid = $options['target_vm_uuid'] ?? null;

        if (empty($newVmUuid)) {
            throw new \Exception('No target VM UUID found. Run recreateVmOnTarget before this step.');
        }

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        // ── Start the VM ──────────────────────────────────────────────────────
        $result = self::performCommand('xe vm-start uuid=' . $newVmUuid, $target);

        if (!empty($result['error'])) {
            throw new \Exception('xe vm-start failed: ' . $result['error']);
        }

        // ── Poll until running (timeout: 3 minutes = 18 × 10s) ───────────────
        $running     = false;
        $powerState  = 'unknown';
        $maxAttempts = 18;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            sleep(10);

            $result     = self::performCommand('xe vm-param-get uuid=' . $newVmUuid . ' param-name=power-state', $target);
            $powerState = trim($result['output'] ?? '');

            Log::info(__METHOD__ . ' | Poll ' . ($attempt + 1) . '/' . $maxAttempts . ' — power-state: ' . $powerState);

            if ($powerState === 'running') {
                $running = true;
                break;
            }

            $progress = 95 + (int) (($attempt / $maxAttempts) * 4); // 95 → 99
            $this->updateStep($migration, 'starting-vm', $progress,
                'Waiting for VM to reach running state... (' . (($attempt + 1) * 10) . 's elapsed)');
        }

        if (!$running) {
            throw new \Exception(
                'VM did not reach running state within 3 minutes. '
                . 'Last power-state: "' . $powerState . '".'
            );
        }

        // ── Update VM status to running (syncDatabaseRecords already set uuid and compute member) ──
        $vm->updateQuietly(['status' => 'running']);

        // ── Mark migration as completed ───────────────────────────────────────
        $migration->updateQuietly([
            'status'       => 'completed',
            'progress'     => 100,
            'current_step' => 'completed',
            'step_message' => 'Migration completed successfully',
            'completed_at' => now(),
        ]);

        Log::info(__METHOD__ . ' | VM started and running on target: ' . $newVmUuid);
    }

    /**
     * Orchestrates all 9 steps in order. Updates migration record progress at each step.
     * Marks migration as failed with an error message if any step throws.
     */
    public function run(VirtualMachineMigrations $migration): void
    {
        $migration->updateQuietly([
            'status'     => 'in-progress',
            'started_at' => now(),
        ]);

        Log::info('[MigrationService] Starting migration: ' . $migration->uuid);

        try {
            $this->preFlightChecks($migration);
            $this->collectSourceMetadata($migration);
            $this->validateAndCoalesceVhd($migration);
            $this->shutdownSourceVm($migration);
            $this->copyVhdFiles($migration);
            $vdiUuidMap = $this->rescanTargetSr($migration);
            $this->recreateVmOnTarget($migration, $vdiUuidMap);
            $this->postMigrationValidation($migration);
            $this->syncDatabaseRecords($migration);
            $this->startVmOnTarget($migration);
        } catch (\Exception $e) {
            Log::error('[MigrationService] Migration ' . $migration->uuid . ' failed at step "'
                . $migration->current_step . '": ' . $e->getMessage());

            $migration->updateQuietly([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Updates the migration record's current step, progress, and step message.
     */
    private function updateStep(
        VirtualMachineMigrations $migration,
        string $step,
        int $progress,
        string $message
    ): void {
        $migration->updateQuietly([
            'current_step' => $step,
            'progress'     => $progress,
            'step_message' => $message,
        ]);

        Log::info('[MigrationService] [' . $step . '] ' . $message);
    }

    /**
     * Executes a command on a compute member via SSH or management agent.
     * Logs the command and result at debug level for easy tracing.
     */
    private static function performCommand(string $command, ComputeMembers $computeMember): array
    {
        logger()->debug('[MigrationService] [ComputeMember:' . $computeMember->name . '] $ ' . $command);

        if ($computeMember->is_management_agent_available) {
            $result = $computeMember->performAgentCommand($command);
        } else {
            $result = $computeMember->performSSHCommand($command);
        }

        logger()->debug('[MigrationService] [ComputeMember:' . $computeMember->name . '] out: ' . trim($result['output'] ?? '')
            . ($result['error'] ? ' | err: ' . trim($result['error']) : ''));

        return $result;
    }

    /**
     * Executes a command on a storage member via SSH.
     * Logs the command and result at debug level for easy tracing.
     */
    private static function performStorageCommand(string $command, StorageMembers $storageMember): array
    {
        logger()->debug('[MigrationService] [StorageMember:' . $storageMember->name . '] $ ' . $command);

        $result = $storageMember->performSSHCommand($command);

        logger()->debug('[MigrationService] [StorageMember:' . $storageMember->name . '] out: ' . trim($result['output'] ?? '')
            . ($result['error'] ? ' | err: ' . trim($result['error']) : ''));

        return $result;
    }

    /**
     * Parses a XenServer map param string ("key1: value1; key2: value2") into an associative array.
     * Used to unpack platform and HVM-boot-params fields collected from xe vm-param-list.
     */
    private static function parseMapString(string $mapString): array
    {
        $result = [];

        foreach (explode(';', $mapString) as $pair) {
            $pair = trim($pair);

            if (empty($pair)) {
                continue;
            }

            $colonPos = strpos($pair, ':');

            if ($colonPos === false) {
                continue;
            }

            $key   = trim(substr($pair, 0, $colonPos));
            $value = trim(substr($pair, $colonPos + 1));

            if (!empty($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Formats a byte count into a human-readable string.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 ** 3) {
            return round($bytes / 1024 ** 3, 2) . ' GB';
        }

        if ($bytes >= 1024 ** 2) {
            return round($bytes / 1024 ** 2, 2) . ' MB';
        }

        return $bytes . ' B';
    }
}
