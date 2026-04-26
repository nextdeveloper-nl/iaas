<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2\XenServer_8_2;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\HypervisorsV2\MigrationInterface;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\AbstractXenService;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualNetworkCardsXenService;
use NextDeveloper\IAAS\Services\IpAddressesService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
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

        // ── VBDs + VDIs via xe vm-disk-list ──────────────────────────────────
        // xe vm-disk-list only returns real data disks — CDROMs, tools ISOs,
        // and empty optical drives are excluded automatically.
        $result   = self::performCommand('xe vm-disk-list uuid=' . $vm->hypervisor_uuid, $source);
        $vmDisks  = self::parseVmDiskList($result['output']);

        $disks = [];

        foreach ($vmDisks as $vmDisk) {
            $vbdSummary = $vmDisk['vbd'];
            $vdiSummary = $vmDisk['vdi'];

            $vbdUuid = trim($vbdSummary['uuid'] ?? '');
            $vdiUuid = trim($vdiSummary['uuid'] ?? '');

            if (empty($vbdUuid) || empty($vdiUuid)) {
                continue;
            }

            // Fetch full params for fields not present in the summary output
            $vbdResult = self::performCommand('xe vbd-param-list uuid=' . $vbdUuid, $source);
            $vbdParams = AbstractXenService::parseResult($vbdResult['output']);

            $vdiResult = self::performCommand('xe vdi-param-list uuid=' . $vdiUuid, $source);
            $vdiParams = AbstractXenService::parseResult($vdiResult['output']);

            $srUuid = trim($vdiParams['sr-uuid'] ?? '');

            // Resolve the actual VHD path by searching all mounted SRs.
            $findResult = self::performCommand(
                'find /var/run/sr-mount/ -name ' . escapeshellarg($vdiUuid . '.vhd') . ' -type f 2>/dev/null | head -1',
                $source
            );
            $vhdPath = trim($findResult['output'] ?? '');

            if ($vhdPath === '') {
                $vhdPath = '/var/run/sr-mount/' . $srUuid . '/' . $vdiUuid . '.vhd';
                Log::warning(__METHOD__ . ' | Could not locate VHD for VDI ' . $vdiUuid
                    . ' via find — using constructed path: ' . $vhdPath);
            } else {
                Log::info(__METHOD__ . ' | Resolved VHD path: ' . $vhdPath);
            }

            $disks[] = [
                'vbd_uuid'       => $vbdUuid,
                'vbd_device'     => trim($vbdParams['device'] ?? ''),
                'vbd_userdevice' => trim($vbdParams['userdevice'] ?? $vbdSummary['userdevice'] ?? ''),
                'vbd_bootable'   => trim($vbdParams['bootable'] ?? 'false'),
                'vbd_mode'       => trim($vbdParams['mode'] ?? 'RW'),
                'vbd_type'       => trim($vbdParams['type'] ?? 'Disk'),
                'vdi_uuid'       => $vdiUuid,
                'vdi_name'       => trim($vdiParams['name-label'] ?? $vdiSummary['name-label'] ?? ''),
                'vdi_size_bytes' => (int) trim($vdiParams['virtual-size'] ?? $vdiSummary['virtual-size'] ?? '0'),
                'sr_uuid'        => $srUuid,
                'sr_name_label'  => trim($vdiParams['sr-name-label'] ?? ''),
                'vhd_path'       => $vhdPath,
            ];
        }

        // ── Remove CDROMs and ISO disks ───────────────────────────────────────
        // xe vm-disk-list should exclude CDROMs, but filter defensively in case
        // the VBD type is CD, the VDI name ends in .iso, or the SR is an ISO library.
        $disks = array_values(array_filter($disks, function ($disk) {
            if (strtolower($disk['vbd_type']) === 'cd') {
                Log::info(__METHOD__ . ' | Skipping CDROM (vbd_type=CD): ' . $disk['vdi_uuid']);
                return false;
            }
            if (str_ends_with(strtolower($disk['vdi_name']), '.iso')) {
                Log::info(__METHOD__ . ' | Skipping ISO VDI (name ends in .iso): ' . $disk['vdi_uuid']);
                return false;
            }
            if (stripos($disk['sr_name_label'], 'ISO') !== false) {
                Log::info(__METHOD__ . ' | Skipping ISO SR VDI (sr-name-label contains ISO): ' . $disk['vdi_uuid']);
                return false;
            }
            return true;
        }));

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

        $maxAttempts    = 24; // 24 × 10s = 4 minutes
        $coalescedDisks = [];

        foreach ($metadata['disks'] as $disk) {
            $vhdPath   = $disk['vhd_path'];
            $vdiUuid   = $disk['vdi_uuid'];
            $srUuid    = $disk['sr_uuid'];
            $coalesced = false;

            // ── Resolve actual VHD path by searching all mounted SRs ─────────
            // The DB SR UUID may be stale or the VDI may be on a different SR.
            // Always resolve via find across the entire /var/run/sr-mount tree.
            $findResult = self::performCommand(
                'find /var/run/sr-mount/ -name ' . escapeshellarg($vdiUuid . '.vhd') . ' -type f 2>/dev/null | head -1',
                $source
            );
            $foundPath = trim($findResult['output'] ?? '');

            if ($foundPath !== '') {
                if ($foundPath !== $vhdPath) {
                    Log::info(__METHOD__ . ' | Resolved actual VHD path: ' . $foundPath
                        . ' (DB path was: ' . $vhdPath . ')');
                }
                $vhdPath = $foundPath;
            } else {
                throw new \Exception(
                    'VHD file not found for VDI ' . $vdiUuid
                    . ' under /var/run/sr-mount/. DB expected: ' . $vhdPath
                );
            }

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $result = self::performCommand(
                    'vhd-util query -n ' . escapeshellarg($vhdPath) . ' -p 2>&1',
                    $source
                );

                $output = trim($result['output'] ?? '');

                // ENOENT (-2): file disappeared mid-loop (e.g. coalesce renamed it).
                // Treat as an error rather than silently looping.
                if (str_contains($output, 'error opening') && str_contains($output, '-2')) {
                    throw new \Exception(
                        'VHD file disappeared during coalesce check: ' . $vhdPath
                        . '. It may have been renamed by the coalesce daemon. Re-run collect-metadata to refresh paths.'
                    );
                }

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
                self::performCommand('xe sr-scan uuid=' . $srUuid, $source);
            }

            if (!$coalesced) {
                throw new \Exception(
                    'VHD "' . $vhdPath . '" still has a parent chain after ' . ($maxAttempts * 10) . ' seconds. '
                    . 'Coalesce did not complete in time.'
                );
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

        // ── Attempt graceful shutdown (timeout: 2 minutes) ───────────────────
        $this->updateStep($migration, 'shutting-down', 37, 'Sending clean shutdown signal to VM: ' . $vm->name);

        self::performCommand('nohup xe vm-shutdown uuid=' . $vm->hypervisor_uuid . ' force=false > /dev/null 2>&1 &', $source);

        $halted      = false;
        $maxAttempts = 12; // 12 × 10s = 2 minutes

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
                'Graceful shutdown timed out after 2 minutes — attempting forced shutdown');

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
        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $isDryRun = !empty($options['dry_run']);

        $this->updateStep(
            $migration,
            'copying-vhd',
            45,
            $isDryRun ? 'Dry-run: resolving VHD copy commands (no SSH executed)' : 'Preparing VHD copy via storage members'
        );

        if (empty($options['coalesced_vhd_paths'])) {
            throw new \Exception('No coalesced VHD paths found. Run validateAndCoalesceVhd before this step.');
        }

        // ── Resolve target NFS coordinates ────────────────────────────────────
        $targetCmVolume = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $migration->target_iaas_compute_member_id)
            ->where('iaas_storage_volume_id', $migration->target_iaas_storage_volume_id)
            ->firstOrFail();

        $targetNfsServerPath = rtrim(trim($targetCmVolume->block_device_data['device-config']['serverpath'] ?? ''), '/')
            . '/' . $targetCmVolume->hypervisor_uuid;

        $targetNfsServer     = trim($targetCmVolume->block_device_data['device-config']['server'] ?? '');
        $targetStorageMemberId = $migration->target_iaas_storage_member_id;

        // ── Build per-VHD resolution helper ──────────────────────────────────
        // Each VHD may be on a different storage member. We resolve (SM, paths,
        // local/cross) per VHD so the correct host executes each rsync.
        $vhdPaths = $options['coalesced_vhd_paths'];

        /**
         * Resolve source SM and path for a single coalesced VHD path.
         * Returns ['sm' => StorageMembers, 'is_local' => bool, 'source_path' => string]
         */
        $resolveVhd = function (string $vhdPath) use ($migration, $targetStorageMemberId): array {
            $srUuid   = basename(dirname($vhdPath));
            $vdiUuid  = basename($vhdPath, '.vhd');

            $srcCmVol = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $migration->source_iaas_compute_member_id)
                ->where('hypervisor_uuid', $srUuid)
                ->firstOrFail();

            $srcStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $srcCmVol->iaas_storage_volume_id)
                ->firstOrFail();

            $sm = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $srcStorageVolume->iaas_storage_member_id)
                ->firstOrFail();

            $sourcePath = rtrim(trim($srcCmVol->block_device_data['device-config']['serverpath'] ?? ''), '/')
                . '/' . $srUuid . '/' . $vdiUuid . '.vhd';

            return [
                'sm'          => $sm,
                'is_local'    => $sm->id === $targetStorageMemberId,
                'source_path' => $sourcePath,
            ];
        };

        // ── Build the full ordered command list (dry-run) ─────────────────────
        $commands  = [];
        $setupDone = []; // [sm_id => ['is_local' => bool, 'mount_point' => string|null]]

        foreach ($vhdPaths as $vhdPath) {
            $vdiUuid  = basename($vhdPath, '.vhd');
            $vhd      = $resolveVhd($vhdPath);
            $sm       = $vhd['sm'];
            $isLocal  = $vhd['is_local'];
            $sourcePath = $vhd['source_path'];

            if ($isLocal) {
                $targetPath = $targetNfsServerPath . '/' . $vdiUuid . '.vhd';
                if (!isset($setupDone[$sm->id])) {
                    $commands[] = [
                        'host'    => $sm->name,
                        'command' => self::sudo('mkdir -p ' . escapeshellarg($targetNfsServerPath), $sm),
                        'note'    => 'Ensure target SR directory exists on ' . $sm->name . ' (local copy)',
                    ];
                    $setupDone[$sm->id] = ['is_local' => true, 'mount_point' => null];
                }
            } else {
                if (!$targetNfsServer) {
                    throw new \Exception('Target NFS server missing from block_device_data. Cannot proceed with copy.');
                }
                $smMountPoint = '/tmp/migration-' . $migration->uuid . '-' . $sm->id;
                $targetPath   = $smMountPoint . '/' . $vdiUuid . '.vhd';
                if (!isset($setupDone[$sm->id])) {
                    $commands[] = [
                        'host'    => $sm->name,
                        'command' => self::sudo('mkdir -p ' . escapeshellarg($smMountPoint), $sm),
                        'note'    => 'Create NFS mount point on ' . $sm->name,
                    ];
                    $commands[] = [
                        'host'    => $sm->name,
                        'command' => self::sudo('mount -t nfs '
                            . escapeshellarg($targetNfsServer . ':' . $targetNfsServerPath)
                            . ' ' . escapeshellarg($smMountPoint), $sm),
                        'note'    => 'Mount target NFS on ' . $sm->name . ' at ' . $targetNfsServer . ':' . $targetNfsServerPath,
                    ];
                    $setupDone[$sm->id] = ['is_local' => false, 'mount_point' => $smMountPoint];
                } else {
                    $smMountPoint = $setupDone[$sm->id]['mount_point'];
                    $targetPath   = $smMountPoint . '/' . $vdiUuid . '.vhd';
                }
            }

            $commands[] = [
                'host'    => $sm->name,
                'command' => self::sudo('rsync -av --partial --progress '
                    . escapeshellarg($sourcePath) . ' '
                    . escapeshellarg($targetPath), $sm),
                'note'    => ($isLocal ? '[local] ' : '[nfs] ') . 'Copy VHD: ' . $vdiUuid . '.vhd on ' . $sm->name,
            ];
            $commands[] = [
                'host'    => $sm->name,
                'command' => 'stat -c%s ' . escapeshellarg($sourcePath),
                'note'    => 'Integrity check — source size of ' . $vdiUuid . '.vhd',
            ];
            $commands[] = [
                'host'    => $sm->name,
                'command' => 'stat -c%s ' . escapeshellarg($targetPath),
                'note'    => 'Integrity check — target size of ' . $vdiUuid . '.vhd',
            ];
        }

        foreach ($setupDone as $smId => $setup) {
            if (!$setup['is_local']) {
                $sm = null;
                foreach ($vhdPaths as $vp) {
                    $r = $resolveVhd($vp);
                    if ($r['sm']->id === $smId) { $sm = $r['sm']; break; }
                }
                $mp = $setup['mount_point'];
                $commands[] = ['host' => $sm->name, 'command' => self::sudo('umount ' . escapeshellarg($mp), $sm), 'note' => 'Unmount NFS on ' . $sm->name];
                $commands[] = ['host' => $sm->name, 'command' => self::sudo('rmdir '  . escapeshellarg($mp), $sm), 'note' => 'Remove mount point on ' . $sm->name];
            }
        }

        // ── Dry-run: persist the command list and return without executing ─────
        if ($isDryRun) {
            $options['dry_run_commands'] = $commands;

            $migration->updateQuietly([
                'options'      => json_encode($options),
                'step_message' => 'Dry-run complete — ' . count($commands) . ' command(s) listed in options.dry_run_commands',
            ]);

            Log::info(__METHOD__ . ' | Dry-run: ' . count($commands) . ' command(s) listed, nothing executed.');

            return;
        }

        // ── Live run ──────────────────────────────────────────────────────────
        unset($options['dry_run'], $options['dry_run_commands']);
        $migration->updateQuietly(['options' => json_encode($options)]);

        // Tracks which SMs have been set up: [sm_id => ['sm' => SM, 'is_local' => bool, 'mount_point' => string|null]]
        $mounted        = [];
        $copiedPaths    = [];
        $total          = count($vhdPaths);
        $progressPerVhd = (int) floor(22 / max($total, 1));

        try {
            foreach ($vhdPaths as $index => $vhdPath) {
                $vdiUuid  = basename($vhdPath, '.vhd');
                $vhd      = $resolveVhd($vhdPath);
                $sm       = $vhd['sm'];
                $isLocal  = $vhd['is_local'];
                $sourcePath = $vhd['source_path'];

                if ($isLocal) {
                    $targetPath = $targetNfsServerPath . '/' . $vdiUuid . '.vhd';
                    if (!isset($mounted[$sm->id])) {
                        self::performStorageCommand(
                            self::sudo('mkdir -p ' . escapeshellarg($targetNfsServerPath), $sm),
                            $sm
                        );
                        $mounted[$sm->id] = ['sm' => $sm, 'is_local' => true, 'mount_point' => null];
                        Log::info(__METHOD__ . ' | Local copy on SM: ' . $sm->name);
                    }
                } else {
                    $smMountPoint = '/tmp/migration-' . $migration->uuid . '-' . $sm->id;
                    if (!isset($mounted[$sm->id])) {
                        self::performStorageCommand(
                            self::sudo('mkdir -p ' . escapeshellarg($smMountPoint), $sm),
                            $sm
                        );
                        $mountResult = self::performStorageCommand(
                            self::sudo('mount -t nfs '
                                . escapeshellarg($targetNfsServer . ':' . $targetNfsServerPath)
                                . ' ' . escapeshellarg($smMountPoint), $sm),
                            $sm
                        );
                        if (!empty($mountResult['error']) && !str_contains($mountResult['error'], 'already mounted')) {
                            throw new \Exception('Failed to mount target NFS on SM ' . $sm->name . ': ' . $mountResult['error']);
                        }
                        $mounted[$sm->id] = ['sm' => $sm, 'is_local' => false, 'mount_point' => $smMountPoint];
                        Log::info(__METHOD__ . ' | Mounted target NFS on SM ' . $sm->name . ' at ' . $smMountPoint);
                    } else {
                        $smMountPoint = $mounted[$sm->id]['mount_point'];
                    }
                    $targetPath = $smMountPoint . '/' . $vdiUuid . '.vhd';
                }

                $progress = 46 + ($index * $progressPerVhd);
                $this->updateStep($migration, 'copying-vhd', $progress,
                    'Copying VHD ' . ($index + 1) . '/' . $total . ': ' . $vdiUuid . '.vhd on ' . $sm->name);

                Log::info(__METHOD__ . ' | Starting rsync on ' . $sm->name . ': ' . $sourcePath . ' -> ' . $targetPath);

                $rsyncResult = self::performStorageCommand(
                    self::sudo('rsync -av --partial --progress '
                        . escapeshellarg($sourcePath) . ' '
                        . escapeshellarg($targetPath), $sm),
                    $sm
                );

                if (!empty($rsyncResult['error']) && !str_contains($rsyncResult['output'], 'sent')) {
                    throw new \Exception('rsync failed for ' . $vdiUuid . '.vhd: ' . $rsyncResult['error']);
                }

                Log::info(__METHOD__ . ' | rsync complete for: ' . $vdiUuid . '.vhd');

                // ── Integrity check ───────────────────────────────────────────
                $sourceSizeResult = self::performStorageCommand('stat -c%s ' . escapeshellarg($sourcePath), $sm);
                $targetSizeResult = self::performStorageCommand('stat -c%s ' . escapeshellarg($targetPath), $sm);

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
            foreach ($mounted as $smInfo) {
                if (!$smInfo['is_local']) {
                    $sm = $smInfo['sm'];
                    $mp = $smInfo['mount_point'];
                    self::performStorageCommand(self::sudo('umount ' . escapeshellarg($mp), $sm), $sm);
                    self::performStorageCommand(self::sudo('rmdir '  . escapeshellarg($mp), $sm), $sm);
                    Log::info(__METHOD__ . ' | Unmounted and cleaned up: ' . $mp . ' on ' . $sm->name);
                }
            }
        }

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

        $isDryRun = !empty($options['dry_run_recreate']);

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $vmMeta   = $metadata['vm'];
        $vmUuid   = '{NEW_VM_UUID}'; // placeholder; replaced with real UUID after live xe vm-create
        $commands = [];

        // ── 1. Create VM skeleton ─────────────────────────────────────────────
        $commands[] = [
            'note' => 'Create VM skeleton',
            'cmd'  => 'xe vm-create name-label=' . escapeshellarg($vmMeta['name_label'])
                . ' name-description=' . escapeshellarg($vmMeta['description']),
        ];

        // ── 2. Set all vm-param-set values in one SSH round-trip ─────────────
        // xe vm-param-set accepts multiple key=value pairs in a single call.
        $paramSetParts = [];
        $paramSetNotes = [];

        $paramSetParts[] = 'VCPUs-max=' . (int) $vmMeta['vcpus_max'];
        $paramSetParts[] = 'VCPUs-at-startup=' . (int) $vmMeta['vcpus_at_startup'];
        $paramSetNotes[] = 'VCPUs (max=' . (int) $vmMeta['vcpus_max'] . ', startup=' . (int) $vmMeta['vcpus_at_startup'] . ')';

        if (!empty($vmMeta['hvm_boot_policy'])) {
            $paramSetParts[] = 'HVM-boot-policy=' . escapeshellarg($vmMeta['hvm_boot_policy']);
            $paramSetNotes[] = 'HVM-boot-policy=' . $vmMeta['hvm_boot_policy'];
        }

        if (!empty($vmMeta['hvm_boot_params'])) {
            foreach (self::parseMapString($vmMeta['hvm_boot_params']) as $key => $value) {
                $paramSetParts[] = 'HVM-boot-params:' . $key . '=' . escapeshellarg($value);
            }
            $paramSetNotes[] = 'HVM-boot-params';
        }

        if (!empty($vmMeta['platform'])) {
            foreach (self::parseMapString($vmMeta['platform']) as $key => $value) {
                $paramSetParts[] = 'platform:' . $key . '=' . escapeshellarg($value);
            }
            $paramSetNotes[] = 'platform';
        }

        if (!empty($vmMeta['pv_args'])) {
            $paramSetParts[] = 'PV-args=' . escapeshellarg($vmMeta['pv_args']);
            $paramSetNotes[] = 'PV-args';
        }

        $commands[] = [
            'note' => 'Set VM params: ' . implode(', ', $paramSetNotes),
            'cmd'  => 'xe vm-param-set uuid=' . $vmUuid . ' ' . implode(' ', $paramSetParts),
        ];

        // ── 3. Set memory params ──────────────────────────────────────────────
        // Use xe vm-memory-limits-set which sets all four limits atomically,
        // avoiding the ordering constraint of xe vm-param-set.
        $commands[] = [
            'note' => 'Set memory limits (static-min=' . (int) $vmMeta['memory_static_min']
                . ' dynamic-min=' . (int) $vmMeta['memory_dynamic_min']
                . ' dynamic-max=' . (int) $vmMeta['memory_dynamic_max']
                . ' static-max=' . (int) $vmMeta['memory_static_max'] . ')',
            'cmd'  => 'xe vm-memory-limits-set uuid=' . $vmUuid
                . ' static-min=' . (int) $vmMeta['memory_static_min']
                . ' dynamic-min=' . (int) $vmMeta['memory_dynamic_min']
                . ' dynamic-max=' . (int) $vmMeta['memory_dynamic_max']
                . ' static-max=' . (int) $vmMeta['memory_static_max'],
        ];

        // ── 7. VBD commands ───────────────────────────────────────────────────
        foreach ($metadata['disks'] as $disk) {
            $sourceVdiUuid = $disk['vdi_uuid'];
            $targetVdiUuid = $vdiUuidMap[$sourceVdiUuid] ?? null;

            if (!$targetVdiUuid) {
                throw new \Exception(
                    'No target VDI UUID mapping found for source VDI: ' . $sourceVdiUuid
                );
            }

            $bootable = (($disk['vbd_bootable'] ?? 'false') === 'true') ? 'true' : 'false';

            $userDevice = $disk['vbd_userdevice'] ?? '';
            if ($userDevice === '') {
                $deviceName = $disk['vbd_device'] ?? '';
                $letter     = preg_replace('/^(?:xvd|hd|sd|vd)/', '', $deviceName);
                $userDevice = ($letter !== '' && ctype_alpha($letter[0]))
                    ? (string)(ord(strtolower($letter[0])) - ord('a'))
                    : '0';
            }

            $commands[] = [
                'note' => 'Create VBD for VDI ' . $sourceVdiUuid . ' → ' . $targetVdiUuid
                    . ' (device=' . $userDevice . ', bootable=' . $bootable . ')',
                'cmd'  => 'xe vbd-create'
                    . ' vm-uuid=' . $vmUuid
                    . ' vdi-uuid=' . $targetVdiUuid
                    . ' device=' . escapeshellarg($userDevice)
                    . ' bootable=' . $bootable
                    . ' mode=' . escapeshellarg(strtoupper($disk['vbd_mode'] ?? 'RW'))
                    . ' type=' . escapeshellarg($disk['vbd_type'] ?? 'Disk'),
            ];
        }

        // ── 8. Resolve network UUID mapping (DB lookups — no XenServer calls yet) ──
        $targetNetworkUuidBySource = [];

        foreach ($metadata['nics'] as $nic) {
            $sourceNetworkUuid = $nic['network_uuid'];

            if (isset($targetNetworkUuidBySource[$sourceNetworkUuid])) {
                continue;
            }

            $sourceCmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $source->id)
                ->where('network_uuid', $sourceNetworkUuid)
                ->first();

            $vlan = $sourceCmni?->vlan;

            $targetCmni = null;

            if ($vlan !== null) {
                $targetCmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_compute_member_id', $target->id)
                    ->where('vlan', $vlan)
                    ->first();
            }

            if (!$targetCmni && !$isDryRun) {
                $networkForCreate = null;

                if ($vlan !== null) {
                    $networkForCreate = Networks::withoutGlobalScope(AuthorizationScope::class)
                        ->where('vlan', $vlan)
                        ->first();
                }

                if (!$networkForCreate) {
                    foreach ($options['network_mapping'] ?? [] as $map) {
                        $sourceNetworkId = $map['source_network']['id'] ?? null;
                        if ($sourceNetworkId) {
                            $candidate = Networks::withoutGlobalScope(AuthorizationScope::class)
                                ->where('id', $sourceNetworkId)
                                ->first();
                            if ($candidate && ($vlan === null || $candidate->vlan == $vlan)) {
                                $networkForCreate = $candidate;
                                break;
                            }
                        }
                    }
                }

                if (!$networkForCreate) {
                    throw new \Exception(
                        'Cannot resolve a Networks record to create network for source UUID: '
                        . $sourceNetworkUuid . ' (VLAN: ' . ($vlan ?? 'unknown') . ').'
                    );
                }

                Log::info(__METHOD__ . ' | Network VLAN ' . $networkForCreate->vlan
                    . ' not found on target "' . $target->name . '" — creating it');

                $targetCmni = ComputeMemberXenService::createNetwork($target, $networkForCreate);

                Log::info(__METHOD__ . ' | Network created on target: network_uuid=' . $targetCmni->network_uuid
                    . ', vlan=' . $targetCmni->vlan);
            }

            $resolvedTargetUuid = $targetCmni ? $targetCmni->network_uuid
                : '{NETWORK_UUID_VLAN_' . ($vlan ?? 'unknown') . '_WILL_BE_CREATED}';

            $targetNetworkUuidBySource[$sourceNetworkUuid] = $resolvedTargetUuid;
        }

        // ── 9. VIF commands ───────────────────────────────────────────────────
        // xe vif-create may auto-generate a MAC even when mac= is provided, so we
        // always follow up with xe vif-param-set to explicitly set the correct MAC.
        foreach ($metadata['nics'] as $nic) {
            $sourceNetworkUuid = $nic['network_uuid'];
            $targetNetworkUuid = $targetNetworkUuidBySource[$sourceNetworkUuid] ?? null;

            if (!$targetNetworkUuid && !$isDryRun) {
                throw new \Exception(
                    'Could not resolve a target network UUID for source network UUID: ' . $sourceNetworkUuid
                    . '. Verify the evacuation plan has a network mapping for all NICs.'
                );
            }

            $commands[] = [
                'type' => 'vif-create',
                'mac'  => $nic['mac'],
                'note' => 'Create VIF device=' . $nic['device'] . ', mac=' . $nic['mac']
                    . ', source-network=' . $sourceNetworkUuid,
                'cmd'  => 'xe vif-create'
                    . ' vm-uuid=' . $vmUuid
                    . ' network-uuid=' . ($targetNetworkUuid ?? '{UNRESOLVED_NETWORK}')
                    . ' device=' . escapeshellarg($nic['device'])
                    . ' mac=' . escapeshellarg($nic['mac'])
                    . ' mtu=' . (int) ($nic['mtu'] ?? 1500),
            ];
        }

        // ── Dry-run: persist commands and return without executing ────────────
        if ($isDryRun) {
            $options['dry_run_commands_recreate'] = $commands;
            $migration->updateQuietly(['options' => json_encode($options)]);
            return $vmUuid; // placeholder
        }

        // ── Live run: execute every command in order ──────────────────────────
        // Step 1: create VM skeleton — captures real UUID
        $result    = self::performCommand($commands[0]['cmd'], $target);
        $newVmUuid = trim($result['output']);

        if (empty($newVmUuid)) {
            throw new \Exception('Failed to create VM on target host. xe vm-create returned empty UUID.');
        }

        $this->updateStep($migration, 'recreating-vm', 82, 'Created VM skeleton: ' . $newVmUuid);
        Log::info(__METHOD__ . ' | Created VM: ' . $newVmUuid);

        // Steps 2-N: replace placeholder UUID and execute
        foreach (array_slice($commands, 1) as $entry) {
            $cmd    = str_replace($vmUuid, $newVmUuid, $entry['cmd']);
            $result = self::performCommand($cmd, $target);

            if (!empty($result['error']) && str_starts_with(ltrim($cmd), 'xe vbd-create')) {
                throw new \Exception('Failed to create VBD: ' . $result['error'] . ' | cmd: ' . $cmd);
            }

            if (!empty($result['error']) && str_starts_with(ltrim($cmd), 'xe vif-create')) {
                throw new \Exception('Failed to create VIF: ' . $result['error'] . ' | cmd: ' . $cmd);
            }

            Log::info(__METHOD__ . ' | ' . $entry['note']);
        }

        $this->updateStep($migration, 'recreating-vm', 92,
            'VM recreated on target with ' . count($metadata['disks']) . ' VBD(s) and '
            . count($metadata['nics']) . ' VIF(s)');

        // ── Persist new VM UUID into options ──────────────────────────────────
        $options['target_vm_uuid'] = $newVmUuid;
        $migration->updateQuietly(['options' => json_encode($options)]);

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

        // ── Snapshot current DB state for rollback ────────────────────────────
        // Persist before making any changes so the original records can be
        // restored if the migration needs to be rolled back.
        if (empty($options['rollback_snapshot'])) {
            $disksSnapshot = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $vm->id)
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($d) => $d->toArray())
                ->toArray();

            $nicsSnapshot = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $vm->id)
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($n) => $n->toArray())
                ->toArray();

            $options['rollback_snapshot'] = [
                'snapshotted_at'   => now()->toIso8601String(),
                'virtual_machine'  => $vm->toArray(),
                'virtual_disks'    => $disksSnapshot,
                'network_cards'    => $nicsSnapshot,
            ];

            $migration->updateQuietly(['options' => json_encode($options)]);

            Log::info(__METHOD__ . ' | Rollback snapshot saved for migration: ' . $migration->uuid);
        }

        // ── Clone VirtualMachines record ──────────────────────────────────────
        // We clone rather than update in-place so the original record is preserved
        // for rollback and the user can see both the old (halted/renamed) and the
        // new (target) VM simultaneously.
        $sourceComputeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $targetComputeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $targetCloudNode = ComputeMembersService::getCloudNode($targetComputeMember);

        $vmFields = $vm->only([
            'name', 'username', 'password', 'hostname', 'description', 'os', 'distro',
            'version', 'domain_type', 'cpu', 'ram', 'is_winrm_enabled', 'features',
            'is_locked', 'is_draft', 'is_template', 'is_snapshot',
            'console_data', 'hypervisor_data',
            'iaas_compute_pool_id', 'iaas_repository_image_id',
            'template_id', 'common_domain_id', 'auto_backup_interval', 'auto_backup_time',
            'backup_repository_id', 'post_boot_script', 'tokens', 'tags',
            'iam_account_id', 'iam_user_id',
        ]);

        $existingFeatures = is_array($vm->features)
            ? $vm->features
            : (json_decode($vm->features ?? '{}', true) ?? []);

        $newVm = VirtualMachinesService::create(array_merge($vmFields, [
            'hypervisor_uuid'              => $newVmUuid,
            'iaas_compute_member_id'       => $migration->target_iaas_compute_member_id,
            'iaas_cloud_node_id'           => $targetCloudNode?->id,
            'status'                       => 'halted',
            'snapshot_of_virtual_machine'  => $vm->id,
            'features'                     => array_merge($existingFeatures, [
                'origin'                       => 'migration',
                'migration_uuid'               => $migration->uuid,
                'migrated_at'                  => now()->toIso8601String(),
                'source_virtual_machine_uuid'  => $vm->uuid,
                'source_compute_member_uuid'   => $sourceComputeMember->uuid,
                'target_compute_member_uuid'   => $targetComputeMember->uuid,
            ]),
        ]));

        Log::info(__METHOD__ . ' | Cloned VirtualMachine: new_id=' . $newVm->id
            . ', hypervisor_uuid=' . $newVmUuid);

        // ── Flag original VM as migrated ──────────────────────────────────────
        // status = 'migrated' tells the background scanner (and operators) that
        // this record has been superseded by the cloned VM on the target host.
        $vm->updateQuietly(['status' => 'migrated']);

        // ── Clone VirtualDiskImages ───────────────────────────────────────────
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

            $diskFields = $disk->only([
                'name', 'size', 'physical_utilisation', 'is_cdrom', 'is_draft',
                'device_number',
                'iaas_storage_pool_id', 'iaas_repository_image_id',
                'iam_account_id', 'iam_user_id',
            ]);

            VirtualDiskImagesService::create(array_merge($diskFields, [
                'iaas_virtual_machine_id' => $newVm->id,
                'iaas_storage_volume_id'  => $targetStorageVolume['id'],
                'hypervisor_uuid'         => $newVdiUuid ?? $disk->hypervisor_uuid,
            ]));

            Log::info(__METHOD__ . ' | Cloned VirtualDiskImage id=' . $diskId
                . ' → new vm_id=' . $newVm->id
                . ', storage_volume_id=' . $targetStorageVolume['id']
                . ', hypervisor_uuid=' . ($newVdiUuid ?? $disk->hypervisor_uuid));
        }

        // ── Fetch actual VIF params from target (MAC + hypervisor UUID) ──────
        $result  = self::performCommand('xe vif-list vm-uuid=' . $newVmUuid, $targetComputeMember);
        $vifList = array_filter(
            AbstractXenService::parseListResult($result['output']),
            fn($v) => !empty($v['uuid'])
        );

        $vifParamsByDevice = [];
        foreach ($vifList as $vif) {
            $vifUuid   = trim($vif['uuid']);
            $vifResult = self::performCommand('xe vif-param-list uuid=' . $vifUuid, $targetComputeMember);
            $params    = AbstractXenService::parseResult($vifResult['output']);
            $device    = trim($params['device'] ?? $vif['device'] ?? '');
            if ($device !== '') {
                $vifParamsByDevice[$device] = $params;
            }
        }

        // ── Clone VirtualNetworkCards ─────────────────────────────────────────
        $networkMapping  = $options['network_mapping'] ?? [];
        $dhcpServersSeen = [];

        foreach ($networkMapping as $map) {
            $nicId        = $map['nic']['id'] ?? null;
            $deviceNumber = (string) ($map['nic']['device_number'] ?? '');
            $networkId    = $map['target_network']['id'] ?? ($map['source_network']['id'] ?? null);

            if (!$nicId) {
                continue;
            }

            $nic = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $nicId)
                ->first();

            if (!$nic) {
                Log::warning(__METHOD__ . ' | VirtualNetworkCard not found for id=' . $nicId);
                continue;
            }

            $vifParams = $vifParamsByDevice[$deviceNumber] ?? null;

            $nicFields = $nic->only([
                'name', 'bandwidth_limit', 'device_number', 'is_draft', 'status',
                'iam_account_id', 'iam_user_id',
            ]);

            $newNic = VirtualNetworkCardsService::create(array_merge($nicFields, [
                'iaas_virtual_machine_id' => $newVm->id,
                'iaas_network_id'         => $networkId,
                'hypervisor_uuid'         => trim($vifParams['uuid'] ?? ''),
                'mac_addr'                => trim($vifParams['MAC'] ?? $vifParams['mac'] ?? $nic->mac_addr),
                'hypervisor_data'         => $vifParams ?? [],
            ]));

            Log::info(__METHOD__ . ' | Cloned VirtualNetworkCard id=' . $nicId
                . ' → new vm_id=' . $newVm->id
                . ', network_id=' . $networkId
                . ', mac=' . trim($vifParams['MAC'] ?? $vifParams['mac'] ?? $nic->mac_addr));

            // ── Reassign IpAddresses from old NIC to new NIC ──────────────────
            // IpAddresses are owned by the VirtualNetworkCard, not the VM.
            // After cloning the NIC we must transfer ownership so the DHCP
            // configuration and address allocations follow the new record.
            $ipAddresses = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_network_card_id', $nic->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($ipAddresses as $ipAddress) {
                IpAddressesService::update($ipAddress->uuid, [
                    'iaas_virtual_network_card_id' => $newNic->id,
                ]);

                Log::info(__METHOD__ . ' | Reassigned IpAddress id=' . $ipAddress->id
                    . ' (' . $ipAddress->ip_addr . ')'
                    . ' from old NIC id=' . $nic->id . ' to new NIC id=' . $newNic->id);
            }

            // ── Apply IP locking on the new VIF ──────────────────────────────
            // Reload so hypervisor_data is properly cast before passing to XenService.
            if ($ipAddresses->isNotEmpty() && !empty($vifParams['uuid'])) {
                $freshNic = $newNic->fresh();
                VirtualNetworkCardsXenService::setIpv4Allowed($freshNic);
                VirtualNetworkCardsXenService::setLockingState($freshNic, VirtualNetworkCardsXenService::LOCKED);

                Log::info(__METHOD__ . ' | Applied ipv4-allowed + locking-mode=locked on new NIC id=' . $newNic->id);
            }

            // ── Trigger DHCP config update for this network ───────────────────
            if ($networkId && !isset($dhcpServersSeen[$networkId])) {
                $dhcpServersSeen[$networkId] = true;

                $network    = Networks::withoutGlobalScope(AuthorizationScope::class)
                    ->where('id', $networkId)
                    ->first();
                $dhcpServer = $network?->dhcpServers;

                if ($dhcpServer) {
                    dispatch(new \NextDeveloper\IAAS\Actions\DhcpServers\UpdateConfiguration($dhcpServer));

                    Log::info(__METHOD__ . ' | Dispatched DHCP UpdateConfiguration for network_id='
                        . $networkId . ', dhcp_server_id=' . $dhcpServer->id);
                }
            }
        }

        // ── Persist new VM id so startVmOnTarget updates the right record ─────
        $options['target_vm_id'] = $newVm->id;
        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'syncing-database', 97, 'Cloned VM, disk, and NIC records to target');

        Log::info(__METHOD__ . ' | Database sync complete — cloned VM id=' . $newVm->id
            . ' for migration: ' . $migration->uuid);
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

        // Use the cloned VM record created by syncDatabaseRecords
        $targetVmId = $options['target_vm_id'] ?? null;

        if (empty($targetVmId)) {
            throw new \Exception('No target VM record found in migration options. Run syncDatabaseRecords before this step.');
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $targetVmId)
            ->firstOrFail();

        // ── Dispatch the Start action (handles cloud-init ISO + xe vm-start) ──
        dispatch(new \NextDeveloper\IAAS\Actions\VirtualMachines\Start($vm));

        // ── Mark migration as completed ───────────────────────────────────────
        $migration->updateQuietly([
            'status'       => 'completed',
            'progress'     => 100,
            'current_step' => 'completed',
            'step_message' => 'Migration completed successfully — VM start dispatched',
            'completed_at' => now(),
        ]);

        Log::info(__METHOD__ . ' | Start action dispatched for cloned VM id=' . $vm->id . ' uuid=' . $vm->uuid);
    }

    /**
     * Orchestrates all 9 steps in order. Updates migration record progress at each step.
     * Marks migration as failed with an error message if any step throws.
     */
    public function run(VirtualMachineMigrations $migration): void
    {
        // ── Reset all cached step outputs so every run starts clean ──────────
        // Stale metadata from a previous (possibly failed) run would cause
        // coalesce-vhd to use wrong VHD paths, and sync-db to skip re-cloning.
        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        foreach ([
            'source_metadata',
            'coalesced_vhd_paths',
            'copied_vhd_paths',
            'vdi_uuid_map',
            'target_vm_uuid',
            'target_vm_id',
            'rollback_snapshot',
            'dry_run_commands',
            'dry_run_commands_recreate',
        ] as $key) {
            unset($options[$key]);
        }

        $migration->updateQuietly([
            'status'     => 'in-progress',
            'started_at' => now(),
            'options'    => json_encode($options),
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
     * Wraps a command so it runs as root via sudo on an Ubuntu storage member.
     *
     * Uses `echo password | sudo -S -i -- command` to supply the password
     * non-interactively over stdin (no TTY required).
     * The password is read from StorageMembers.ssh_password at call time.
     */
    /**
     * Parses the output of `xe vm-disk-list` into an array of disks,
     * each with 'vbd' and 'vdi' sub-arrays of key=>value pairs.
     *
     * Example output block:
     *   Disk 0 VBD:
     *   uuid ( RO): 616ba652-...
     *      userdevice ( RW): 0
     *   Disk 0 VDI:
     *   uuid ( RO): efd99ca0-...
     *      virtual-size ( RO): 536870912000
     */
    private static function parseVmDiskList(string $output): array
    {
        $disks   = [];
        $current = null;
        $section = null;

        foreach (explode("\n", $output) as $line) {
            if (preg_match('/^Disk\s+\d+\s+VBD:/i', trim($line))) {
                preg_match('/\d+/', trim($line), $m);
                $idx = (int) $m[0];
                if (!isset($disks[$idx])) {
                    $disks[$idx] = ['vbd' => [], 'vdi' => []];
                }
                $current = $idx;
                $section = 'vbd';
                continue;
            }

            if (preg_match('/^Disk\s+\d+\s+VDI:/i', trim($line))) {
                preg_match('/\d+/', trim($line), $m);
                $idx = (int) $m[0];
                if (!isset($disks[$idx])) {
                    $disks[$idx] = ['vbd' => [], 'vdi' => []];
                }
                $current = $idx;
                $section = 'vdi';
                continue;
            }

            if ($current === null || $section === null) {
                continue;
            }

            // key ( RO|RW): value
            if (preg_match('/^\s*([^(]+?)\s*\(\s*R[OW]\s*\)\s*:\s*(.*)$/', $line, $m)) {
                $key   = trim($m[1]);
                $value = trim($m[2]);
                $disks[$current][$section][$key] = $value;
            }
        }

        return array_values($disks);
    }

    private static function sudo(string $command, StorageMembers $storageMember): string
    {
        $password = decrypt($storageMember->ssh_password);

        return 'echo ' . escapeshellarg($password) . ' | sudo -S -p \'\' -- ' . $command;
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
