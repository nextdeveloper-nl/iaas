<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2\XenServer_8_2;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\HypervisorsV2\MigrationInterface;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\AbstractXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualNetworkCardsXenService;
use NextDeveloper\IAAS\Services\IpAddressesService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Local-disk-to-local-disk VM migration for XenServer 8.2.
 *
 * Differs from MigrationService (NFS-based) in three areas:
 *  - preFlightChecks: verifies local SR mount paths instead of NFS mounts,
 *    and verifies passwordless SSH from source hypervisor to target hypervisor.
 *  - copyVhdFiles: rsyncs VHDs directly between hypervisors over SSH
 *    (no storage member, no NFS mount intermediary).
 *  - rescanTargetSr: same xe sr-scan logic; local SR paths are still under
 *    /var/run/sr-mount/ for EXT-type SRs, so no structural change needed.
 *
 * All other steps are identical to MigrationService.
 */
class LocalDiskMigrationService implements MigrationInterface
{
    private const KNOWN_POWER_STATES = ['halted', 'running', 'paused', 'suspended'];

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1
    // ─────────────────────────────────────────────────────────────────────────

    public function preFlightChecks(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'pre-flight-checks', 0, 'Starting pre-flight checks (local disk migration)');

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

        $srPhysicalSize  = (int) trim($result['output']);
        $result          = self::performCommand(
            'xe sr-param-get uuid=' . $targetStorageVolume->hypervisor_uuid . ' param-name=physical-utilisation',
            $target
        );
        $srFreeBytes = $srPhysicalSize - (int) trim($result['output']);

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

        // ── CHECK 5: Source local SR is attached and mount path is accessible ───
        $this->updateStep($migration, 'pre-flight-checks', 9, 'Verifying source local SR mount path');

        $sourceStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_storage_volume_id)
            ->firstOrFail();

        $sourceSrPath = $this->resolveLocalSrMountPath($sourceStorageVolume->hypervisor_uuid, $source);

        if (!$sourceSrPath) {
            throw new \Exception(
                'Source local SR "' . $sourceStorageVolume->hypervisor_uuid . '" is not attached or its mount '
                . 'directory could not be found under /var/run/sr-mount on host "' . $source->name . '". '
                . 'Verify the SR is of type EXT, is attached to the host, and the PBD is plugged.'
            );
        }

        Log::info(__METHOD__ . ' | Source SR path OK: ' . $sourceSrPath);

        // ── CHECK 6: Target local SR is attached and mount path is accessible ───
        $this->updateStep($migration, 'pre-flight-checks', 10, 'Verifying target local SR mount path');

        $targetSrPath = $this->resolveLocalSrMountPath($targetStorageVolume->hypervisor_uuid, $target);

        if (!$targetSrPath) {
            throw new \Exception(
                'Target local SR "' . $targetStorageVolume->hypervisor_uuid . '" is not attached or its mount '
                . 'directory could not be found under /var/run/sr-mount on host "' . $target->name . '". '
                . 'Verify the SR is of type EXT, is attached to the host, and the PBD is plugged.'
            );
        }

        Log::info(__METHOD__ . ' | Target SR path OK: ' . $targetSrPath);

        // Persist the resolved target SR mount path so copyVhdFiles doesn't have to re-derive it.
        $options                      = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);
        $options['target_sr_mount_path'] = $targetSrPath;
        $migration->updateQuietly(['options' => json_encode($options)]);

        // ── CHECK 7: Source hypervisor can reach target hypervisor via SSH ─────
        // Required for the direct rsync-over-SSH in copyVhdFiles.
        $this->updateStep($migration, 'pre-flight-checks', 11,
            'Verifying SSH reachability from source to target hypervisor');

        $targetIp = explode('/', $target->ip_addr)[0];

        $result = self::performCommand(
            'ssh -o BatchMode=yes -o StrictHostKeyChecking=no -o ConnectTimeout=10 '
            . escapeshellarg('root@' . $targetIp) . ' echo ok 2>&1',
            $source
        );

        if (trim($result['output']) !== 'ok') {
            throw new \Exception(
                'Source host "' . $source->name . '" cannot reach target host "' . $target->name . '" ('
                . $targetIp . ') via passwordless SSH. '
                . 'Ensure SSH key authentication is configured between the two hypervisors.'
            );
        }

        $this->updateStep($migration, 'pre-flight-checks', 12, 'Pre-flight checks passed');

        Log::info(__METHOD__ . ' | All pre-flight checks passed for migration: ' . $migration->uuid);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 — identical to MigrationService
    // ─────────────────────────────────────────────────────────────────────────

    public function collectSourceMetadata(VirtualMachineMigrations $migration): array
    {
        $this->updateStep($migration, 'collecting-metadata', 10, 'Collecting source VM metadata');

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        $result   = self::performCommand('xe vm-param-list uuid=' . $vm->hypervisor_uuid, $source);
        $vmParams = AbstractXenService::parseResult($result['output']);

        $this->updateStep($migration, 'collecting-metadata', 12, 'Collected VM params');

        $result  = self::performCommand('xe vm-disk-list uuid=' . $vm->hypervisor_uuid, $source);
        $vmDisks = self::parseVmDiskList($result['output']);

        $disks = [];

        foreach ($vmDisks as $vmDisk) {
            $vbdSummary = $vmDisk['vbd'];
            $vdiSummary = $vmDisk['vdi'];

            $vbdUuid = trim($vbdSummary['uuid'] ?? '');
            $vdiUuid = trim($vdiSummary['uuid'] ?? '');

            if (empty($vbdUuid) || empty($vdiUuid)) {
                continue;
            }

            $vbdResult = self::performCommand('xe vbd-param-list uuid=' . $vbdUuid, $source);
            $vbdParams = AbstractXenService::parseResult($vbdResult['output']);

            $vdiResult = self::performCommand('xe vdi-param-list uuid=' . $vdiUuid, $source);
            $vdiParams = AbstractXenService::parseResult($vdiResult['output']);

            $srUuid = trim($vdiParams['sr-uuid'] ?? '');

            // Determine SR type by checking for the LVM VG directly.
            // xe sr-param-get type is unreliable (varies: lvm, lvm_vhd, lvmoiscsi…).
            // LVM SRs: VHD is an LV at /dev/VG_XenStorage-<sr-uuid>/VHD-<vdi-uuid>
            // EXT SRs: VHD is a file under /var/run/sr-mount/<sr-uuid>/<vdi-uuid>.vhd
            $vgName   = 'VG_XenStorage-' . $srUuid;
            $vgExists = trim(self::performCommand(
                'test -d ' . escapeshellarg('/dev/' . $vgName) . ' && echo ok || echo fail',
                $source
            )['output'] ?? '');

            if ($vgExists !== 'ok') {
                // VG may exist but be inactive — activate and re-check.
                self::performCommand('vgchange -ay ' . escapeshellarg($vgName) . ' 2>/dev/null', $source);
                $vgExists = trim(self::performCommand(
                    'test -d ' . escapeshellarg('/dev/' . $vgName) . ' && echo ok || echo fail',
                    $source
                )['output'] ?? '');
            }

            if ($vgExists === 'ok') {
                $vhdPath = '/dev/' . $vgName . '/VHD-' . $vdiUuid;
                Log::info(__METHOD__ . ' | LVM VHD path: ' . $vhdPath);
            } else {
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
                    Log::info(__METHOD__ . ' | EXT VHD path: ' . $vhdPath);
                }
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

        $options                    = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);
        $options['source_metadata'] = $metadata;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'collecting-metadata', 20, 'Source metadata collected and saved');

        Log::info(__METHOD__ . ' | Metadata collected: '
            . count($disks) . ' disk(s), ' . count($nics) . ' NIC(s)');

        return $metadata;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3 — identical to MigrationService
    // ─────────────────────────────────────────────────────────────────────────

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

        $result    = self::performCommand('xe snapshot-list snapshot-of=' . $vm->hypervisor_uuid . ' params=uuid', $source);
        $snapshots = AbstractXenService::parseListResult($result['output']);
        $snapshots = array_filter($snapshots, fn($s) => !empty($s['uuid']));

        if (!empty($snapshots)) {
            $snapshotUuids = array_map('trim', array_column($snapshots, 'uuid'));

            Log::warning(__METHOD__ . ' | VM "' . $vm->name . '" has ' . count($snapshotUuids) . ' snapshot(s): '
                . implode(', ', $snapshotUuids));

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

        $sourceStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_storage_volume_id)
            ->firstOrFail();

        self::performCommand('xe sr-scan uuid=' . $sourceStorageVolume->hypervisor_uuid, $source);

        $this->updateStep($migration, 'validating-vhd', 27, 'Waiting for VHD coalesce on source SR');

        $metadata = $options['source_metadata'] ?? null;

        if (empty($metadata['disks'])) {
            throw new \Exception('No disk metadata found. Run collectSourceMetadata before this step.');
        }

        $maxAttempts    = 24;
        $coalescedDisks = [];

        foreach ($metadata['disks'] as $disk) {
            $vhdPath   = $disk['vhd_path'];
            $vdiUuid   = $disk['vdi_uuid'];
            $srUuid    = $disk['sr_uuid'];
            $coalesced = false;

            // LVM SRs expose VHDs as block devices — the path from collectSourceMetadata
            // already points to /dev/VG_XenStorage-<sr>/VHD-<vdi>. Verify it exists.
            $isLvm = str_starts_with($vhdPath, '/dev/VG_XenStorage-');

            if ($isLvm) {
                $exists = trim(self::performCommand(
                    'test -b ' . escapeshellarg($vhdPath) . ' && echo ok || echo fail',
                    $source
                )['output'] ?? '');

                if ($exists !== 'ok') {
                    // Try activating the VG first.
                    self::performCommand(
                        'vgchange -ay ' . escapeshellarg('VG_XenStorage-' . $srUuid) . ' 2>/dev/null',
                        $source
                    );

                    $exists = trim(self::performCommand(
                        'test -b ' . escapeshellarg($vhdPath) . ' && echo ok || echo fail',
                        $source
                    )['output'] ?? '');
                }

                if ($exists !== 'ok') {
                    throw new \Exception(
                        'LVM LV not found for VDI ' . $vdiUuid . ': ' . $vhdPath
                        . '. Ensure the VG VG_XenStorage-' . $srUuid . ' is active.'
                    );
                }

                // LVM LVs use VHD format internally and CAN have parent chains
                // (snapshots create child LVs pointing to a parent LV).
                // Fall through to the vhd-util coalesce check below.
            } else {
                // EXT SR — resolve the actual file path first.
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
            }

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $result = self::performCommand(
                    'vhd-util query -n ' . escapeshellarg($vhdPath) . ' -p 2>&1',
                    $source
                );

                $output = trim($result['output'] ?? '');

                if (str_contains($output, 'error opening') && str_contains($output, '-2')) {
                    throw new \Exception(
                        'VHD file disappeared during coalesce check: ' . $vhdPath
                        . '. It may have been renamed by the coalesce daemon. Re-run collect-metadata to refresh paths.'
                    );
                }

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

        $options['coalesced_vhd_paths'] = $coalescedDisks;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'validating-vhd', 35,
            'VHD validation complete — ' . count($coalescedDisks) . ' flat VHD(s) ready for copy');

        Log::info(__METHOD__ . ' | All VHDs coalesced and verified for migration: ' . $migration->uuid);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4 — identical to MigrationService
    // ─────────────────────────────────────────────────────────────────────────

    public function shutdownSourceVm(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'shutting-down', 35, 'Initiating graceful shutdown of source VM');

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        $result     = self::performCommand('xe vm-param-get uuid=' . $vm->hypervisor_uuid . ' param-name=power-state', $source);
        $powerState = trim($result['output'] ?? '');

        if ($powerState === 'halted') {
            $this->updateStep($migration, 'shutting-down', 45, 'VM is already halted — skipping shutdown');
            Log::info(__METHOD__ . ' | VM "' . $vm->name . '" is already halted.');
            return;
        }

        $this->updateStep($migration, 'shutting-down', 37, 'Sending clean shutdown signal to VM: ' . $vm->name);

        self::performCommand('nohup xe vm-shutdown uuid=' . $vm->hypervisor_uuid . ' force=false > /dev/null 2>&1 &', $source);

        $halted      = false;
        $maxAttempts = 12;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            sleep(10);

            $result     = self::performCommand('xe vm-param-get uuid=' . $vm->hypervisor_uuid . ' param-name=power-state', $source);
            $powerState = trim($result['output'] ?? '');

            Log::info(__METHOD__ . ' | Poll ' . ($attempt + 1) . '/' . $maxAttempts . ' — power-state: ' . $powerState);

            if ($powerState === 'halted') {
                $halted = true;
                break;
            }

            $progress = 37 + (int) (($attempt / $maxAttempts) * 6);
            $this->updateStep($migration, 'shutting-down', $progress,
                'Waiting for VM to halt... (' . (($attempt + 1) * 10) . 's elapsed)');
        }

        if (!$halted) {
            $this->updateStep($migration, 'shutting-down', 43,
                'Graceful shutdown timed out after 2 minutes — attempting forced shutdown');

            Log::warning(__METHOD__ . ' | Graceful shutdown timed out for VM "' . $vm->name . '". Forcing shutdown.');

            self::performCommand('xe vm-shutdown uuid=' . $vm->hypervisor_uuid . ' force=true', $source);

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

        $vm->updateQuietly(['status' => 'halted']);

        $this->updateStep($migration, 'shutting-down', 45, 'VM halted successfully');

        Log::info(__METHOD__ . ' | VM "' . $vm->name . '" is halted. Proceeding with migration.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 5 — mixed copy: local SR → direct hypervisor rsync;
    //           NFS SR → local rsync on storage member (same SM)
    //                   or SM-to-SM rsync over SSH (different SMs)
    // ─────────────────────────────────────────────────────────────────────────

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
            $isDryRun ? 'Dry-run: resolving VHD copy commands (mixed local/NFS)' : 'Preparing VHD copy (mixed local/NFS)'
        );

        if (empty($options['coalesced_vhd_paths'])) {
            throw new \Exception('No coalesced VHD paths found. Run validateAndCoalesceVhd before this step.');
        }

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $targetStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_storage_volume_id)
            ->firstOrFail();

        $targetIp       = explode('/', $target->ip_addr)[0];
        $targetLocalDir = $options['target_sr_mount_path']
            ?? $this->resolveLocalSrMountPath($targetStorageVolume->hypervisor_uuid, $target)
            ?? ('/var/run/sr-mount/' . $targetStorageVolume->hypervisor_uuid);
        $storageMapping = $options['storage_mapping'] ?? [];
        $vhdPaths       = $options['coalesced_vhd_paths'];

        $allCommands    = [];
        $copiedPaths    = [];
        $total          = count($vhdPaths);
        $progressPerVhd = (int) floor(22 / max($total, 1));

        foreach ($vhdPaths as $index => $vhdPath) {
            // LVM paths look like /dev/VG_XenStorage-<sr-uuid>/VHD-<vdi-uuid>.
            // EXT paths look like /var/run/sr-mount/<sr-uuid>/<vdi-uuid>.vhd.
            $isLvmSource = str_starts_with($vhdPath, '/dev/VG_XenStorage-');
            $vdiUuid     = $isLvmSource
                ? substr(basename($vhdPath), 4)        // strip leading 'VHD-'
                : basename($vhdPath, '.vhd');

            // ── Determine SR type from the evacuation plan's storage_mapping ──
            // The VHD filename is the hypervisor UUID; storage_mapping keys by DB id,
            // so look up the VirtualDiskImage record first and match by id.
            $dbDisk = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $vdiUuid)
                ->where('iaas_virtual_machine_id', $migration->iaas_virtual_machine_id)
                ->first();

            $diskMapping = null;
            if ($dbDisk) {
                foreach ($storageMapping as $mapping) {
                    if (($mapping['disk']['id'] ?? null) == $dbDisk->id) {
                        $diskMapping = $mapping;
                        break;
                    }
                }
            }

            $sourceStorageType = $diskMapping['source_storage_volume']['disk_physical_type'] ?? 'local';
            $isNfs             = $sourceStorageType === 'nfs';

            if (!$isNfs && $isLvmSource) {
                // ── Strategy A2: LVM SR → lvcreate on target + dd over SSH ────
                // LVM LVs are raw block devices; dd is more reliable than rsync --inplace
                // across hosts. We must create the target LV first.
                $lvName     = 'VHD-' . $vdiUuid;
                $tmpLvName  = 'TMP-' . $vdiUuid;   // hidden from SMAPI until copy is complete
                $vgName     = basename($targetLocalDir);
                $tmpLvPath  = $targetLocalDir . '/' . $tmpLvName;
                $targetLvPath = $targetLocalDir . '/' . $lvName;

                $sizeCmd     = 'blockdev --getsize64 ' . escapeshellarg($vhdPath);
                $ssh         = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes ' . escapeshellarg('root@' . $targetIp);
                $createLvCmd = $ssh . ' lvcreate -L $(blockdev --getsize64 ' . escapeshellarg($vhdPath) . ')B'
                    . ' -n ' . escapeshellarg($tmpLvName)
                    . ' ' . escapeshellarg($vgName)
                    . ' 2>/dev/null || true';
                // rsync --inplace uses stat() to determine block device size, which returns 0
                // on Linux LVs — so rsync copies nothing. Use dd with an explicit block count
                // derived from blockdev --getsize64, which reads the true device size.
                // Writing to TMP-<uuid> keeps SMAPI from seeing a partial VHD mid-copy.
                $rsyncLvCmd  = 'dd if=' . escapeshellarg($vhdPath) . ' bs=64M'
                    . ' count=$(( $(blockdev --getsize64 ' . escapeshellarg($vhdPath) . ') / 64 / 1024 / 1024 + 1 ))'
                    . ' | ssh -o StrictHostKeyChecking=no -o BatchMode=yes '
                    . escapeshellarg('root@' . $targetIp)
                    . ' dd of=' . escapeshellarg($tmpLvPath) . ' bs=64M';
                $renameCmd   = $ssh . ' lvrename '
                    . escapeshellarg($vgName . '/' . $tmpLvName) . ' '
                    . escapeshellarg($lvName);

                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => $sizeCmd,
                    'note'    => '[LVM SR] Get source LV size: ' . $lvName,
                ];
                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => $createLvCmd,
                    'note'    => '[LVM SR] Create temp LV ' . $tmpLvName . ' on target (hidden from SMAPI)',
                ];
                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => $rsyncLvCmd,
                    'note'    => '[LVM SR] rsync --inplace ' . $vhdPath . ' → ' . $targetIp . ':' . $tmpLvPath,
                ];
                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => $renameCmd,
                    'note'    => '[LVM SR] Rename ' . $tmpLvName . ' → ' . $lvName . ' (make visible to SMAPI)',
                ];
                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => 'blockdev --getsize64 ' . escapeshellarg($vhdPath),
                    'note'    => '[LVM SR] Integrity check — source size',
                ];
                $allCommands[] = [
                    'host'    => $target->name,
                    'command' => 'blockdev --getsize64 ' . escapeshellarg($targetLvPath),
                    'note'    => '[LVM SR] Integrity check — target size',
                ];

                if (!$isDryRun) {
                    $progress = 46 + ($index * $progressPerVhd);
                    $this->updateStep($migration, 'copying-vhd', $progress,
                        'Copying LVM VHD ' . ($index + 1) . '/' . $total . ': ' . $lvName);

                    $sourceSize = (int) trim(self::performCommand($sizeCmd, $source)['output']);

                    if ($sourceSize === 0) {
                        throw new \Exception('[LVM SR] Source LV size is 0 for ' . $lvName . ' — LV may be missing or inactive.');
                    }

                    // If VHD-<uuid> already exists (previous run completed the rename),
                    // skip straight to integrity check — the copy is already done.
                    $vhdExists = trim(self::performCommand(
                        $ssh . ' test -b ' . escapeshellarg($targetLvPath) . ' && echo yes || echo no',
                        $source
                    )['output'] ?? '');

                    if ($vhdExists === 'yes') {
                        Log::info(__METHOD__ . ' | [LVM SR] ' . $lvName . ' already exists on target — skipping copy.');
                        $targetSize = (int) trim(self::performCommand(
                            'blockdev --getsize64 ' . escapeshellarg($targetLvPath), $target
                        )['output']);
                        $copiedPaths[] = [
                            'vdi_uuid'    => $vdiUuid,
                            'source_path' => $vhdPath,
                            'target_path' => $targetLvPath,
                            'size_bytes'  => $sourceSize,
                            'copy_type'   => 'lvm',
                        ];
                        continue;
                    }

                    Log::info(__METHOD__ . ' | [LVM SR] Creating temp LV ' . $tmpLvName . ' (' . $this->formatBytes($sourceSize) . ')');
                    self::performCommand($createLvCmd, $source);

                    Log::info(__METHOD__ . ' | [LVM SR] dd copy: ' . $vhdPath . ' → ' . $targetIp . ':' . $tmpLvPath);
                    self::performCommand($rsyncLvCmd, $source);

                    // dd writes progress stats to stderr — size integrity check below catches any partial copy

                    $targetSize = (int) trim(self::performCommand(
                        'blockdev --getsize64 ' . escapeshellarg($tmpLvPath), $target
                    )['output']);

                    if ($sourceSize !== $targetSize) {
                        throw new \Exception('[LVM SR] Integrity check failed for ' . $lvName . ': '
                            . 'source=' . $this->formatBytes($sourceSize) . ', target=' . $this->formatBytes($targetSize));
                    }

                    Log::info(__METHOD__ . ' | [LVM SR] Integrity OK — renaming ' . $tmpLvName . ' → ' . $lvName);
                    self::performCommand($renameCmd, $source);

                    Log::info(__METHOD__ . ' | [LVM SR] Copy complete: ' . $lvName . ' (' . $this->formatBytes($sourceSize) . ')');

                    $copiedPaths[] = [
                        'vdi_uuid'    => $vdiUuid,
                        'source_path' => $vhdPath,
                        'target_path' => $targetLvPath,
                        'size_bytes'  => $sourceSize,
                        'copy_type'   => 'lvm',
                    ];
                }
            } elseif (!$isNfs) {
                // ── Strategy A1: EXT SR → direct rsync between hypervisors ────
                $targetPath = $targetLocalDir . '/' . $vdiUuid . '.vhd';

                $rsyncCmd = 'rsync -av --partial -e '
                    . escapeshellarg('ssh -o StrictHostKeyChecking=no -o BatchMode=yes')
                    . ' ' . escapeshellarg($vhdPath)
                    . ' ' . escapeshellarg('root@' . $targetIp . ':' . $targetPath);

                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => $rsyncCmd,
                    'note'    => '[EXT SR] Copy VHD: ' . $vdiUuid . '.vhd → ' . $targetIp . ':' . $targetPath,
                ];
                $allCommands[] = [
                    'host'    => $source->name,
                    'command' => 'stat -c%s ' . escapeshellarg($vhdPath),
                    'note'    => '[EXT SR] Integrity check — source size of ' . $vdiUuid . '.vhd',
                ];
                $allCommands[] = [
                    'host'    => $target->name,
                    'command' => 'stat -c%s ' . escapeshellarg($targetPath),
                    'note'    => '[EXT SR] Integrity check — target size of ' . $vdiUuid . '.vhd',
                ];

                if (!$isDryRun) {
                    $progress = 46 + ($index * $progressPerVhd);
                    $this->updateStep($migration, 'copying-vhd', $progress,
                        'Copying EXT VHD ' . ($index + 1) . '/' . $total . ': ' . $vdiUuid . '.vhd');

                    Log::info(__METHOD__ . ' | [EXT SR] rsync: ' . $vhdPath . ' → ' . $targetIp . ':' . $targetPath);

                    $rsyncResult = self::performCommand($rsyncCmd, $source);

                    if (!empty($rsyncResult['error']) && !str_contains($rsyncResult['output'], 'sent')) {
                        throw new \Exception('[EXT SR] rsync failed for ' . $vdiUuid . '.vhd: ' . $rsyncResult['error']);
                    }

                    $sourceSize = (int) trim(self::performCommand('stat -c%s ' . escapeshellarg($vhdPath), $source)['output']);
                    $targetSize = (int) trim(self::performCommand('stat -c%s ' . escapeshellarg($targetPath), $target)['output']);

                    if ($sourceSize === 0) {
                        throw new \Exception('[EXT SR] Source VHD size is 0 for ' . $vdiUuid . '.vhd — file may be missing.');
                    }
                    if ($sourceSize !== $targetSize) {
                        throw new \Exception('[EXT SR] Integrity check failed for ' . $vdiUuid . '.vhd: '
                            . 'source=' . $this->formatBytes($sourceSize) . ', target=' . $this->formatBytes($targetSize));
                    }

                    Log::info(__METHOD__ . ' | [EXT SR] Integrity OK: ' . $vdiUuid . '.vhd (' . $this->formatBytes($sourceSize) . ')');

                    $copiedPaths[] = [
                        'vdi_uuid'    => $vdiUuid,
                        'source_path' => $vhdPath,
                        'target_path' => $targetPath,
                        'size_bytes'  => $sourceSize,
                        'copy_type'   => 'local',
                    ];
                }
            } else {
                // ── Strategy B: NFS SR → copy on storage member(s) ───────────
                // Resolving paths on the storage member avoids routing data over
                // the network twice (hypervisor → NFS server → hypervisor → NFS server).
                // Instead we SSH into the SM and rsync within it (or SM-to-SM).

                $sourceStorageVolumeId = $diskMapping['source_storage_volume']['id'] ?? null;
                $targetStorageVolumeId = $diskMapping['target_storage_volume']['id'] ?? null;

                if (!$sourceStorageVolumeId || !$targetStorageVolumeId) {
                    throw new \Exception(
                        '[NFS SR] Storage volume IDs missing in storage_mapping for disk ' . $vdiUuid
                        . '. Re-run the propose/approve step.'
                    );
                }

                // Source NFS coordinates
                $sourceCmVol = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_compute_member_id', $migration->source_iaas_compute_member_id)
                    ->where('iaas_storage_volume_id', $sourceStorageVolumeId)
                    ->firstOrFail();

                $sourceSm = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
                    ->where('id', $sourceCmVol->iaas_storage_member_id ?? $migration->source_iaas_storage_member_id)
                    ->firstOrFail();

                $sourceSmPath = rtrim($sourceCmVol->block_device_data['device-config']['serverpath'] ?? '', '/')
                    . '/' . ($diskMapping['source_storage_volume']['hypervisor_uuid'] ?? '');

                // Target NFS coordinates
                $targetCmVol = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iaas_compute_member_id', $migration->target_iaas_compute_member_id)
                    ->where('iaas_storage_volume_id', $targetStorageVolumeId)
                    ->firstOrFail();

                $targetSmId = $targetCmVol->iaas_storage_member_id ?? $migration->target_iaas_storage_member_id;

                $targetSmPath = rtrim($targetCmVol->block_device_data['device-config']['serverpath'] ?? '', '/')
                    . '/' . ($diskMapping['target_storage_volume']['hypervisor_uuid'] ?? '');

                $sourceVhdOnSm = $sourceSmPath . '/' . $vdiUuid . '.vhd';
                $targetVhdOnSm = $targetSmPath . '/' . $vdiUuid . '.vhd';

                $sameSm = (string) $sourceSm->id === (string) $targetSmId;

                if ($sameSm) {
                    // Both SRs live on the same physical storage server — pure local copy.
                    $rsyncCmd = self::sudo(
                        'rsync -av --partial ' . escapeshellarg($sourceVhdOnSm) . ' ' . escapeshellarg($targetVhdOnSm),
                        $sourceSm
                    );

                    $allCommands[] = [
                        'host'    => $sourceSm->name,
                        'command' => $rsyncCmd,
                        'note'    => '[NFS SR, same SM] Local copy on ' . $sourceSm->name . ': ' . $vdiUuid . '.vhd',
                    ];
                    $allCommands[] = [
                        'host'    => $sourceSm->name,
                        'command' => 'stat -c%s ' . escapeshellarg($sourceVhdOnSm),
                        'note'    => '[NFS SR, same SM] Integrity check — source size',
                    ];
                    $allCommands[] = [
                        'host'    => $sourceSm->name,
                        'command' => 'stat -c%s ' . escapeshellarg($targetVhdOnSm),
                        'note'    => '[NFS SR, same SM] Integrity check — target size',
                    ];
                } else {
                    // Different storage servers — rsync from source SM to target SM via SSH.
                    $targetSm = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
                        ->where('id', $targetSmId)
                        ->firstOrFail();

                    $rsyncCmd = self::sudo(
                        'rsync -av --partial -e '
                            . escapeshellarg('ssh -o StrictHostKeyChecking=no -o BatchMode=yes')
                            . ' ' . escapeshellarg($sourceVhdOnSm)
                            . ' ' . escapeshellarg('root@' . explode('/', $targetSm->ip_addr)[0] . ':' . $targetVhdOnSm),
                        $sourceSm
                    );

                    $allCommands[] = [
                        'host'    => $sourceSm->name,
                        'command' => $rsyncCmd,
                        'note'    => '[NFS SR, cross-SM] rsync ' . $sourceSm->name . ' → ' . $targetSm->name . ': ' . $vdiUuid . '.vhd',
                    ];
                    $allCommands[] = [
                        'host'    => $sourceSm->name,
                        'command' => 'stat -c%s ' . escapeshellarg($sourceVhdOnSm),
                        'note'    => '[NFS SR, cross-SM] Integrity check — source size',
                    ];
                    $allCommands[] = [
                        'host'    => $targetSm->name,
                        'command' => 'stat -c%s ' . escapeshellarg($targetVhdOnSm),
                        'note'    => '[NFS SR, cross-SM] Integrity check — target size (on target SM)',
                    ];
                }

                if (!$isDryRun) {
                    $copyLabel = $sameSm ? 'local on SM' : 'cross-SM';
                    $progress  = 46 + ($index * $progressPerVhd);

                    $this->updateStep($migration, 'copying-vhd', $progress,
                        'Copying NFS VHD ' . ($index + 1) . '/' . $total . ': ' . $vdiUuid . '.vhd (' . $copyLabel . ')');

                    Log::info(__METHOD__ . ' | [NFS SR] copy (' . $copyLabel . '): '
                        . $sourceVhdOnSm . ' → ' . $targetVhdOnSm);

                    $rsyncResult = self::performStorageCommand($rsyncCmd, $sourceSm);

                    if (!empty($rsyncResult['error']) && !str_contains($rsyncResult['output'], 'sent')) {
                        throw new \Exception('[NFS SR] rsync failed for ' . $vdiUuid . '.vhd: ' . $rsyncResult['error']);
                    }

                    $sourceSize = (int) trim(
                        self::performStorageCommand('stat -c%s ' . escapeshellarg($sourceVhdOnSm), $sourceSm)['output']
                    );

                    $targetSize = $sameSm
                        ? (int) trim(self::performStorageCommand('stat -c%s ' . escapeshellarg($targetVhdOnSm), $sourceSm)['output'])
                        : (int) trim(self::performStorageCommand('stat -c%s ' . escapeshellarg($targetVhdOnSm), $targetSm)['output']);

                    if ($sourceSize === 0) {
                        throw new \Exception('[NFS SR] Source VHD size is 0 for ' . $vdiUuid . '.vhd — file may be missing.');
                    }
                    if ($sourceSize !== $targetSize) {
                        throw new \Exception('[NFS SR] Integrity check failed for ' . $vdiUuid . '.vhd: '
                            . 'source=' . $this->formatBytes($sourceSize) . ', target=' . $this->formatBytes($targetSize));
                    }

                    Log::info(__METHOD__ . ' | [NFS SR] Integrity OK: ' . $vdiUuid . '.vhd (' . $this->formatBytes($sourceSize) . ')');

                    $copiedPaths[] = [
                        'vdi_uuid'    => $vdiUuid,
                        'source_path' => $sourceVhdOnSm,
                        'target_path' => $targetVhdOnSm,
                        'size_bytes'  => $sourceSize,
                        'copy_type'   => $sameSm ? 'nfs-local' : 'nfs-cross-sm',
                    ];
                }
            }
        }

        // ── Dry-run: persist full command list and return ─────────────────────
        if ($isDryRun) {
            $options['dry_run_commands'] = $allCommands;

            $migration->updateQuietly([
                'options'      => json_encode($options),
                'step_message' => 'Dry-run complete — ' . count($allCommands) . ' command(s) listed in options.dry_run_commands',
            ]);

            Log::info(__METHOD__ . ' | Dry-run: ' . count($allCommands) . ' command(s) listed, nothing executed.');

            return;
        }

        unset($options['dry_run'], $options['dry_run_commands']);
        $options['copied_vhd_paths'] = $copiedPaths;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'copying-vhd', 70, 'All ' . count($copiedPaths) . ' VHD(s) copied and verified');

        Log::info(__METHOD__ . ' | VHD copy complete for migration: ' . $migration->uuid);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 6 — identical to MigrationService
    // ─────────────────────────────────────────────────────────────────────────

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

        self::performCommand('xe sr-scan uuid=' . $targetSrUuid, $target);

        sleep(5);

        $this->updateStep($migration, 'rescanning-sr', 73, 'Querying VDI list on target SR');

        $result  = self::performCommand('xe vdi-list sr-uuid=' . $targetSrUuid . ' params=uuid,name-label,virtual-size', $target);
        $vdiList = AbstractXenService::parseListResult($result['output']);

        $targetVdisByUuid = [];

        foreach ($vdiList as $vdi) {
            $uuid = trim($vdi['uuid'] ?? '');
            if (!empty($uuid)) {
                $targetVdisByUuid[$uuid] = $vdi;
            }
        }

        Log::info(__METHOD__ . ' | Found ' . count($targetVdisByUuid) . ' VDI(s) in target SR after scan');

        $vdiUuidMap = [];
        $unmatched  = [];

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

        $options['vdi_uuid_map'] = $vdiUuidMap;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'rescanning-sr', 80,
            'SR scan complete — ' . count($vdiUuidMap) . ' VDI(s) confirmed on target');

        Log::info(__METHOD__ . ' | VDI map: ' . json_encode($vdiUuidMap));

        return $vdiUuidMap;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEPS 7–10 — identical to MigrationService
    // ─────────────────────────────────────────────────────────────────────────

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
        $vmUuid   = '{NEW_VM_UUID}';
        $commands = [];

        // ── VM skeleton ───────────────────────────────────────────────────────
        $commands[] = [
            'note' => 'Create VM skeleton',
            'cmd'  => 'xe vm-create name-label=' . escapeshellarg($vmMeta['name_label'])
                . ' name-description=' . escapeshellarg($vmMeta['description'] ?? ''),
        ];

        $commands[] = [
            'note' => 'Set vCPU count',
            'cmd'  => 'xe vm-param-set uuid={VM_UUID}'
                . ' VCPUs-max=' . (int) $vmMeta['vcpus_max']
                . ' VCPUs-at-startup=' . (int) $vmMeta['vcpus_at_startup'],
        ];

        $commands[] = [
            'note' => 'Set memory limits',
            'cmd'  => 'xe vm-memory-limits-set uuid={VM_UUID}'
                . ' static-min=' . (int) $vmMeta['memory_static_min']
                . ' static-max=' . (int) $vmMeta['memory_static_max']
                . ' dynamic-min=' . (int) $vmMeta['memory_dynamic_min']
                . ' dynamic-max=' . (int) $vmMeta['memory_dynamic_max'],
        ];

        if (!empty($vmMeta['hvm_boot_policy'])) {
            $commands[] = [
                'note' => 'Set HVM boot policy',
                'cmd'  => 'xe vm-param-set uuid={VM_UUID} HVM-boot-policy=' . escapeshellarg($vmMeta['hvm_boot_policy']),
            ];
        }

        if (!empty($vmMeta['hvm_boot_params'])) {
            $commands[] = [
                'note' => 'Set HVM boot params',
                'cmd'  => 'xe vm-param-set uuid={VM_UUID} HVM-boot-params=' . escapeshellarg($vmMeta['hvm_boot_params']),
            ];
        }

        if (!empty($vmMeta['platform'])) {
            $commands[] = [
                'note' => 'Set platform params',
                'cmd'  => 'xe vm-param-set uuid={VM_UUID} platform=' . escapeshellarg($vmMeta['platform']),
            ];
        }

        if (!empty($vmMeta['pv_args'])) {
            $commands[] = [
                'note' => 'Set PV args',
                'cmd'  => 'xe vm-param-set uuid={VM_UUID} PV-args=' . escapeshellarg($vmMeta['pv_args']),
            ];
        }

        // ── VBDs ──────────────────────────────────────────────────────────────
        foreach ($metadata['disks'] as $disk) {
            $targetVdiUuid = $vdiUuidMap[$disk['vdi_uuid']] ?? null;

            if (!$targetVdiUuid) {
                throw new \Exception('No target VDI UUID found for source VDI: ' . $disk['vdi_uuid']);
            }

            $commands[] = [
                'note' => 'Create VBD for VDI ' . $targetVdiUuid,
                'cmd'  => 'xe vbd-create vm-uuid={VM_UUID}'
                    . ' vdi-uuid=' . escapeshellarg($targetVdiUuid)
                    . ' device=' . escapeshellarg($disk['vbd_userdevice'])
                    . ' bootable=' . ($disk['vbd_bootable'] === 'true' ? 'true' : 'false')
                    . ' mode=' . escapeshellarg($disk['vbd_mode'])
                    . ' type=' . escapeshellarg($disk['vbd_type']),
            ];
        }

        // ── VIFs ──────────────────────────────────────────────────────────────
        $networkMapping = $options['network_mapping'] ?? [];

        foreach ($metadata['nics'] as $nic) {
            $targetNetworkUuid = null;

            foreach ($networkMapping as $mapping) {
                if (($mapping['nic']['vif_uuid'] ?? null) === $nic['vif_uuid']) {
                    $targetNetworkUuid = $mapping['target_network']['hypervisor_uuid'] ?? null;
                    break;
                }
            }

            if (!$targetNetworkUuid) {
                // Try to resolve via ComputeMemberNetworkInterfaces on the target host,
                // matching by the target network name stored in the migration plan.
                $targetNetworkName = $mapping['target_network']['name'] ?? null;
                if ($targetNetworkName) {
                    $targetCmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                        ->where('iaas_compute_member_id', $target->id)
                        ->where('network_name', $targetNetworkName)
                        ->first();
                    $targetNetworkUuid = $targetCmni?->network_uuid ?: null;
                }
            }

            if (!$targetNetworkUuid) {
                // Ask the target host directly by bridge name
                $result = self::performCommand(
                    'xe network-list bridge=' . escapeshellarg('xenbr' . ($nic['device'] ?? '0')) . ' --minimal',
                    $target
                );
                $targetNetworkUuid = trim($result['output'] ?? '');
            }

            if (!$targetNetworkUuid) {
                // Network does not exist on target — create it using the source network definition
                $sourceNetworkId = $mapping['source_network']['id'] ?? null;
                $sourceNetworkModel = $sourceNetworkId
                    ? Networks::withoutGlobalScope(AuthorizationScope::class)->find($sourceNetworkId)
                    : null;

                if (!$sourceNetworkModel) {
                    throw new \Exception(
                        'Cannot resolve target network for NIC device=' . $nic['device']
                        . ' (source network-uuid: ' . $nic['network_uuid'] . '). '
                        . 'Ensure network mapping is correct in the migration plan.'
                    );
                }

                Log::info(__METHOD__ . ' | Network not found on target — creating: ' . $sourceNetworkModel->name);
                $newCmni = ComputeMemberXenService::createNetwork($target, $sourceNetworkModel);
                $targetNetworkUuid = $newCmni->network_uuid;
            }

            $commands[] = [
                'note' => 'Create VIF device=' . $nic['device'],
                'cmd'  => 'xe vif-create vm-uuid={VM_UUID}'
                    . ' network-uuid=' . escapeshellarg($targetNetworkUuid)
                    . ' device=' . escapeshellarg($nic['device'])
                    . ' mac=' . escapeshellarg($nic['mac'])
                    . ' mtu=' . (int) $nic['mtu'],
            ];
        }

        // ── Dry-run ───────────────────────────────────────────────────────────
        if ($isDryRun) {
            $options['dry_run_commands_recreate'] = $commands;
            $migration->updateQuietly(['options' => json_encode($options)]);
            Log::info(__METHOD__ . ' | Dry-run: ' . count($commands) . ' recreate command(s) listed.');
            return $vmUuid;
        }

        // ── Live: create VM skeleton ──────────────────────────────────────────
        $createResult = self::performCommand(
            'xe vm-create name-label=' . escapeshellarg($vmMeta['name_label'])
                . ' name-description=' . escapeshellarg($vmMeta['description'] ?? ''),
            $target
        );

        $vmUuid = trim($createResult['output'] ?? '');

        if (empty($vmUuid)) {
            throw new \Exception('xe vm-create returned empty UUID. Output: ' . $createResult['output']);
        }

        Log::info(__METHOD__ . ' | Created VM skeleton: ' . $vmUuid);

        // Set vCPUs
        self::performCommand(
            'xe vm-param-set uuid=' . $vmUuid
                . ' VCPUs-max=' . (int) $vmMeta['vcpus_max']
                . ' VCPUs-at-startup=' . (int) $vmMeta['vcpus_at_startup'],
            $target
        );

        // Set memory
        self::performCommand(
            'xe vm-memory-limits-set uuid=' . $vmUuid
                . ' static-min=' . (int) $vmMeta['memory_static_min']
                . ' static-max=' . (int) $vmMeta['memory_static_max']
                . ' dynamic-min=' . (int) $vmMeta['memory_dynamic_min']
                . ' dynamic-max=' . (int) $vmMeta['memory_dynamic_max'],
            $target
        );

        if (!empty($vmMeta['hvm_boot_policy'])) {
            self::performCommand(
                'xe vm-param-set uuid=' . $vmUuid . ' HVM-boot-policy=' . escapeshellarg($vmMeta['hvm_boot_policy']),
                $target
            );
        }

        if (!empty($vmMeta['hvm_boot_params'])) {
            self::performCommand(
                'xe vm-param-set uuid=' . $vmUuid . ' HVM-boot-params=' . escapeshellarg($vmMeta['hvm_boot_params']),
                $target
            );
        }

        if (!empty($vmMeta['platform'])) {
            self::performCommand(
                'xe vm-param-set uuid=' . $vmUuid . ' platform=' . escapeshellarg($vmMeta['platform']),
                $target
            );
        }

        if (!empty($vmMeta['pv_args'])) {
            self::performCommand(
                'xe vm-param-set uuid=' . $vmUuid . ' PV-args=' . escapeshellarg($vmMeta['pv_args']),
                $target
            );
        }

        // Create VBDs
        foreach ($metadata['disks'] as $disk) {
            $targetVdiUuid = $vdiUuidMap[$disk['vdi_uuid']] ?? null;

            if (!$targetVdiUuid) {
                throw new \Exception('No target VDI UUID found for source VDI: ' . $disk['vdi_uuid']);
            }

            $result = self::performCommand(
                'xe vbd-create vm-uuid=' . $vmUuid
                    . ' vdi-uuid=' . escapeshellarg($targetVdiUuid)
                    . ' device=' . escapeshellarg($disk['vbd_userdevice'])
                    . ' bootable=' . ($disk['vbd_bootable'] === 'true' ? 'true' : 'false')
                    . ' mode=' . escapeshellarg($disk['vbd_mode'])
                    . ' type=' . escapeshellarg($disk['vbd_type']),
                $target
            );

            if (!empty($result['error'])) {
                throw new \Exception('Failed to create VBD for VDI ' . $targetVdiUuid . ': ' . $result['error']);
            }

            Log::info(__METHOD__ . ' | VBD created for VDI: ' . $targetVdiUuid);
        }

        // Create VIFs
        foreach ($metadata['nics'] as $nic) {
            $targetNetworkUuid = null;
            $nicMapping = null;

            foreach ($networkMapping as $mapping) {
                if (($mapping['nic']['vif_uuid'] ?? null) === $nic['vif_uuid']) {
                    $targetNetworkUuid = $mapping['target_network']['hypervisor_uuid'] ?? null;
                    $nicMapping = $mapping;
                    break;
                }
            }

            if (!$targetNetworkUuid) {
                // Try ComputeMemberNetworkInterfaces on target by stored network name
                $targetNetworkName = $nicMapping['target_network']['name'] ?? null;
                if ($targetNetworkName) {
                    $targetCmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                        ->where('iaas_compute_member_id', $target->id)
                        ->where('network_name', $targetNetworkName)
                        ->first();
                    $targetNetworkUuid = $targetCmni?->network_uuid ?: null;
                }
            }

            if (!$targetNetworkUuid) {
                // Ask target host by bridge name — --minimal returns just the UUID
                $result = self::performCommand(
                    'xe network-list bridge=' . escapeshellarg('xenbr' . ($nic['device'] ?? '0')) . ' --minimal',
                    $target
                );
                $targetNetworkUuid = trim($result['output'] ?? '');
            }

            if (!$targetNetworkUuid) {
                // Network does not exist on target — create it using the source network definition
                $sourceNetworkId = $nicMapping['source_network']['id'] ?? null;
                $sourceNetworkModel = $sourceNetworkId
                    ? Networks::withoutGlobalScope(AuthorizationScope::class)->find($sourceNetworkId)
                    : null;

                if (!$sourceNetworkModel) {
                    throw new \Exception(
                        'Cannot resolve target network for NIC device=' . $nic['device']
                        . ' (source network-uuid: ' . $nic['network_uuid'] . ').'
                    );
                }

                Log::info(__METHOD__ . ' | Network not found on target — creating: ' . $sourceNetworkModel->name);
                $newCmni = ComputeMemberXenService::createNetwork($target, $sourceNetworkModel);
                $targetNetworkUuid = $newCmni->network_uuid;
            }

            Log::info(__METHOD__ . ' | Creating VIF device=' . $nic['device'] . ' with network-uuid=' . $targetNetworkUuid);

            $result = self::performCommand(
                'xe vif-create vm-uuid=' . $vmUuid
                    . ' network-uuid=' . escapeshellarg($targetNetworkUuid)
                    . ' device=' . escapeshellarg($nic['device'])
                    . ' mac=' . escapeshellarg($nic['mac'])
                    . ' mtu=' . (int) $nic['mtu'],
                $target
            );

            if (!empty($result['error'])) {
                throw new \Exception('Failed to create VIF device=' . $nic['device'] . ': ' . $result['error']);
            }

            Log::info(__METHOD__ . ' | VIF created: device=' . $nic['device'] . ' mac=' . $nic['mac']);
        }

        $options['target_vm_uuid'] = $vmUuid;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'recreating-vm', 90, 'VM recreated on target: ' . $vmUuid);

        Log::info(__METHOD__ . ' | VM recreated on target: ' . $vmUuid);

        return $vmUuid;
    }

    public function postMigrationValidation(VirtualMachineMigrations $migration): array
    {
        $this->updateStep($migration, 'validating', 90, 'Running post-migration validation');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $targetVmUuid = $options['target_vm_uuid'] ?? null;

        if (!$targetVmUuid || $targetVmUuid === '{NEW_VM_UUID}') {
            throw new \Exception('No target VM UUID found. Run recreateVmOnTarget before this step.');
        }

        $target   = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $metadata = $options['source_metadata'] ?? [];
        $vmMeta   = $metadata['vm'] ?? [];

        $result    = self::performCommand('xe vm-param-list uuid=' . $targetVmUuid, $target);
        $vmParams  = AbstractXenService::parseResult($result['output']);

        $result   = self::performCommand('xe vbd-list vm-uuid=' . $targetVmUuid . ' params=uuid', $target);
        $vbdList  = AbstractXenService::parseListResult($result['output']);
        $vbdCount = count(array_filter($vbdList, fn($v) => !empty($v['uuid'])));

        $result   = self::performCommand('xe vif-list vm-uuid=' . $targetVmUuid . ' params=uuid', $target);
        $vifList  = AbstractXenService::parseListResult($result['output']);
        $vifCount = count(array_filter($vifList, fn($v) => !empty($v['uuid'])));

        $checks = [
            'vcpus'  => [
                'expected' => (int) ($vmMeta['vcpus_max'] ?? 0),
                'actual'   => (int) trim($vmParams['VCPUs-max'] ?? '0'),
                'pass'     => (int) ($vmMeta['vcpus_max'] ?? 0) === (int) trim($vmParams['VCPUs-max'] ?? '0'),
            ],
            'memory' => [
                'expected' => (int) ($vmMeta['memory_static_max'] ?? 0),
                'actual'   => (int) trim($vmParams['memory-static-max'] ?? '0'),
                'pass'     => (int) ($vmMeta['memory_static_max'] ?? 0) === (int) trim($vmParams['memory-static-max'] ?? '0'),
            ],
            'disks'  => [
                'expected' => count($metadata['disks'] ?? []),
                'actual'   => $vbdCount,
                'pass'     => count($metadata['disks'] ?? []) === $vbdCount,
            ],
            'nics'   => [
                'expected' => count($metadata['nics'] ?? []),
                'actual'   => $vifCount,
                'pass'     => count($metadata['nics'] ?? []) === $vifCount,
            ],
        ];

        $isValid = array_reduce($checks, fn($carry, $check) => $carry && $check['pass'], true);

        $summary = ['is_valid' => $isValid, 'checks' => $checks];

        if (!$isValid) {
            Log::warning(__METHOD__ . ' | Validation FAILED: ' . json_encode($checks));
        } else {
            Log::info(__METHOD__ . ' | Validation passed for VM: ' . $targetVmUuid);
        }

        $this->updateStep($migration, 'validating', 92,
            $isValid ? 'Validation passed' : 'Validation FAILED — review checks');

        return $summary;
    }

    public function syncDatabaseRecords(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'syncing-database', 92, 'Syncing database records to target');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $targetVmUuid = $options['target_vm_uuid'] ?? null;

        if (empty($targetVmUuid) || $targetVmUuid === '{NEW_VM_UUID}') {
            throw new \Exception('No target VM UUID found. Run recreateVmOnTarget before this step.');
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        $sourceComputeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $targetComputeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        // ── Rollback snapshot (guard against re-creation on retry) ────────────
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
                'snapshotted_at'  => now()->toIso8601String(),
                'virtual_machine' => $vm->toArray(),
                'virtual_disks'   => $disksSnapshot,
                'network_cards'   => $nicsSnapshot,
            ];

            $migration->updateQuietly(['options' => json_encode($options)]);

            Log::info(__METHOD__ . ' | Rollback snapshot saved for migration: ' . $migration->uuid);
        }

        // ── Clone VirtualMachines record ──────────────────────────────────────
        $targetCloudNode = ComputeMembersService::getCloudNode($targetComputeMember);

        $existingFeatures = is_array($vm->features)
            ? $vm->features
            : (json_decode($vm->features ?? '{}', true) ?? []);

        $newVm = VirtualMachinesService::create(array_merge(
            $vm->only([
                'name', 'username', 'password', 'hostname', 'description', 'os', 'distro',
                'version', 'domain_type', 'cpu', 'ram', 'is_winrm_enabled',
                'is_locked', 'is_draft', 'is_template', 'is_snapshot',
                'console_data', 'hypervisor_data',
                'iaas_compute_pool_id', 'iaas_repository_image_id',
                'template_id', 'common_domain_id', 'auto_backup_interval', 'auto_backup_time',
                'backup_repository_id', 'post_boot_script', 'tokens', 'tags',
                'iam_account_id', 'iam_user_id',
            ]),
            [
                'hypervisor_uuid'             => $targetVmUuid,
                'iaas_compute_member_id'      => $migration->target_iaas_compute_member_id,
                'iaas_cloud_node_id'          => $targetCloudNode?->id,
                'status'                      => 'halted',
                'snapshot_of_virtual_machine' => $vm->id,
                'features'                    => array_merge($existingFeatures, [
                    'origin'                      => 'migration',
                    'migration_uuid'              => $migration->uuid,
                    'migration_type'              => 'local-disk',
                    'migrated_at'                 => now()->toIso8601String(),
                    'source_virtual_machine_uuid' => $vm->uuid,
                    'source_compute_member_uuid'  => $sourceComputeMember->uuid,
                    'target_compute_member_uuid'  => $targetComputeMember->uuid,
                ]),
            ]
        ));

        Log::info(__METHOD__ . ' | Cloned VirtualMachine: new_id=' . $newVm->id . ', hypervisor_uuid=' . $targetVmUuid);

        // ── Flag original VM as migrated ──────────────────────────────────────
        $vm->updateQuietly(['status' => 'migrated']);

        // ── Clone VirtualDiskImages from storage_mapping ──────────────────────
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

            VirtualDiskImagesService::create(array_merge(
                $disk->only([
                    'name', 'size', 'physical_utilisation', 'is_cdrom', 'is_draft',
                    'device_number', 'iaas_storage_pool_id', 'iaas_repository_image_id',
                    'iam_account_id', 'iam_user_id',
                ]),
                [
                    'iaas_virtual_machine_id' => $newVm->id,
                    'iaas_storage_volume_id'  => $targetStorageVolume['id'],
                    'hypervisor_uuid'         => $newVdiUuid ?? $disk->hypervisor_uuid,
                ]
            ));

            Log::info(__METHOD__ . ' | Cloned VirtualDiskImage id=' . $diskId
                . ' → new vm_id=' . $newVm->id
                . ', storage_volume_id=' . $targetStorageVolume['id']
                . ', hypervisor_uuid=' . ($newVdiUuid ?? $disk->hypervisor_uuid));
        }

        // ── Fetch actual VIF params from target hypervisor ────────────────────
        $result  = self::performCommand('xe vif-list vm-uuid=' . $targetVmUuid, $targetComputeMember);
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

        // ── Clone VirtualNetworkCards from network_mapping ────────────────────
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

            $newNic = VirtualNetworkCardsService::create(array_merge(
                $nic->only([
                    'name', 'bandwidth_limit', 'device_number', 'is_draft', 'status',
                    'iam_account_id', 'iam_user_id',
                ]),
                [
                    'iaas_virtual_machine_id' => $newVm->id,
                    'iaas_network_id'         => $networkId,
                    'hypervisor_uuid'         => trim($vifParams['uuid'] ?? ''),
                    'mac_addr'                => trim($vifParams['MAC'] ?? $vifParams['mac'] ?? $nic->mac_addr),
                    'hypervisor_data'         => $vifParams ?? [],
                ]
            ));

            Log::info(__METHOD__ . ' | Cloned VirtualNetworkCard id=' . $nicId
                . ' → new vm_id=' . $newVm->id
                . ', network_id=' . $networkId
                . ', mac=' . trim($vifParams['MAC'] ?? $vifParams['mac'] ?? $nic->mac_addr));

            // ── Reassign IpAddresses from old NIC to new NIC ──────────────────
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
                    . ' from NIC id=' . $nic->id . ' to new NIC id=' . $newNic->id);
            }

            // ── Apply IP locking on the new VIF ──────────────────────────────
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

    public function startVmOnTarget(VirtualMachineMigrations $migration): void
    {
        $this->updateStep($migration, 'starting-vm', 95, 'Starting VM on target host');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $targetVmId = $options['target_vm_id'] ?? null;

        if (!$targetVmId) {
            throw new \Exception('No target VM DB id found. Run syncDatabaseRecords before this step.');
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $targetVmId)
            ->firstOrFail();

        VirtualMachinesService::start($vm);

        $migration->updateQuietly([
            'status'       => 'completed',
            'completed_at' => now(),
            'step_message' => 'Migration completed successfully',
        ]);

        $this->updateStep($migration, 'starting-vm', 100, 'VM started — migration complete');

        Log::info(__METHOD__ . ' | Start action dispatched for cloned VM id=' . $vm->id . ' uuid=' . $vm->uuid);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ORCHESTRATOR
    // ─────────────────────────────────────────────────────────────────────────

    public function run(VirtualMachineMigrations $migration): void
    {
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

        Log::info('[LocalDiskMigrationService] Starting migration: ' . $migration->uuid);

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
            Log::error('[LocalDiskMigrationService] Migration ' . $migration->uuid . ' failed at step "'
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
     * Resolves the base storage path for a local SR on a compute member.
     *
     * LVM SR  → /dev/VG_XenStorage-<sr-uuid>  (LVM VG, VHDs are LVs named VHD-<vdi-uuid>)
     * EXT SR  → /var/run/sr-mount/<sr-uuid>    (directory, VHD files inside)
     *
     * Detection order:
     *   1. Check for LVM VG directly via vgdisplay — more reliable than xe sr-param-get type,
     *      which varies across XenServer versions (lvm, lvm_vhd, lvmoiscsi, etc.) and whose
     *      currently-attached field is a PBD attribute not an SR attribute so it reads empty.
     *   2. Fall back to finding the EXT mount directory under /var/run/sr-mount.
     *
     * Returns null if neither path can be confirmed on the host.
     */
    private function resolveLocalSrMountPath(string $srUuid, ComputeMembers $host): ?string
    {
        $vgName = 'VG_XenStorage-' . $srUuid;
        $vgPath = '/dev/' . $vgName;

        // Activate the VG in case it exists but is inactive, then check for it.
        self::performCommand('vgchange -ay ' . escapeshellarg($vgName) . ' 2>/dev/null', $host);

        $vgExists = trim(self::performCommand(
            'test -d ' . escapeshellarg($vgPath) . ' && echo ok || echo fail',
            $host
        )['output'] ?? '');

        if ($vgExists === 'ok') {
            Log::info(__METHOD__ . ' | LVM VG found: ' . $vgPath . ' on ' . $host->name);
            return $vgPath;
        }

        // Not LVM — look for an EXT/ext3/ext4 mount directory.
        $found = trim(self::performCommand(
            'find /var/run/sr-mount -maxdepth 1 -type d -name ' . escapeshellarg($srUuid) . ' 2>/dev/null | head -1',
            $host
        )['output'] ?? '');

        if ($found !== '') {
            Log::info(__METHOD__ . ' | EXT SR mount found: ' . $found . ' on ' . $host->name);
            return $found;
        }

        Log::warning(__METHOD__ . ' | SR ' . $srUuid . ': no LVM VG at ' . $vgPath
            . ' and no EXT mount under /var/run/sr-mount on ' . $host->name);

        return null;
    }

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

        Log::info('[LocalDiskMigrationService] [' . $step . '] ' . $message);
    }

    private static function performCommand(string $command, ComputeMembers $computeMember): array
    {
        logger()->debug('[LocalDiskMigrationService] [ComputeMember:' . $computeMember->name . '] $ ' . $command);

        $result = $computeMember->is_management_agent_available
            ? $computeMember->performAgentCommand($command)
            : $computeMember->performSSHCommand($command);

        logger()->debug('[LocalDiskMigrationService] [ComputeMember:' . $computeMember->name . '] out: '
            . trim($result['output'] ?? '')
            . ($result['error'] ? ' | err: ' . trim($result['error']) : ''));

        return $result;
    }

    private static function performStorageCommand(string $command, StorageMembers $storageMember): array
    {
        logger()->debug('[LocalDiskMigrationService] [StorageMember:' . $storageMember->name . '] $ ' . $command);

        $result = $storageMember->performSSHCommand($command);

        logger()->debug('[LocalDiskMigrationService] [StorageMember:' . $storageMember->name . '] out: '
            . trim($result['output'] ?? '')
            . ($result['error'] ? ' | err: ' . trim($result['error']) : ''));

        return $result;
    }

    private static function sudo(string $command, StorageMembers $storageMember): string
    {
        $password = decrypt($storageMember->ssh_password);

        return 'echo ' . escapeshellarg($password) . ' | sudo -S -p \'\' -- ' . $command;
    }

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

            if (preg_match('/^\s*([^(]+?)\s*\(\s*R[OW]\s*\)\s*:\s*(.*)$/', $line, $m)) {
                $disks[$current][$section][trim($m[1])] = trim($m[2]);
            }
        }

        return array_values($disks);
    }

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
