<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\MigrationInterface;
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

            // xe vif-list may omit MAC — fetch it via vif-param-list
            $vifUuid = trim($vif['uuid']);
            $mac = trim($vif['MAC'] ?? $vif['mac'] ?? '');
            if (empty($mac)) {
                $vifParamResult = self::performCommand('xe vif-param-list uuid=' . $vifUuid, $source);
                $vifParams = AbstractXenService::parseResult($vifParamResult['output']);
                $mac = trim($vifParams['MAC'] ?? $vifParams['mac'] ?? '');
            }

            // Get the VLAN from the source network so we can find the matching network on target
            $srcNetworkUuid = trim($vif['network-uuid'] ?? '');
            $vlan = null;
            if ($srcNetworkUuid) {
                // Resolve VLAN via the PIF attached to this network
                $pifResult = self::performCommand(
                    'xe pif-list network-uuid=' . escapeshellarg($srcNetworkUuid) . ' --minimal',
                    $source
                );
                $pifUuid = trim($pifResult['output'] ?? '');
                if ($pifUuid) {
                    $vlanResult = self::performCommand(
                        'xe pif-param-get uuid=' . escapeshellarg($pifUuid) . ' param-name=VLAN',
                        $source
                    );
                    $vlanVal = (int) trim($vlanResult['output'] ?? '-1');
                    $vlan = $vlanVal >= 0 ? $vlanVal : null;
                }
            }

            $nics[] = [
                'vif_uuid'     => $vifUuid,
                'device'       => trim($vif['device'] ?? ''),
                'mac'          => $mac,
                'network_uuid' => $srcNetworkUuid,
                'vlan'         => $vlan,
                'mtu'          => (int) trim($vif['MTU'] ?? $vif['mtu'] ?? '1500'),
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
    // STEP 5 — Stream the whole VM (disks + metadata) straight from source to
    //           target with `xe vm-export | ssh ... xe vm-import`, instead of
    //           rsyncing raw VHD files and reconstructing the VM by hand.
    //           xe handles the SR-specific disk format (LVM/EXT) internally,
    //           so this works uniformly regardless of source/target SR type —
    //           the file-copy approach below this comment used to fail here
    //           because it depended on brittle VHD-path/coalesce assumptions
    //           per SR type. `--preserve` keeps VIF MAC addresses from source;
    //           network attachment is still fixed up in recreateVmOnTarget()
    //           since imported VIFs reference source network UUIDs that
    //           usually don't exist on the target pool.
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
            $isDryRun ? 'Dry-run: resolving export/import command' : 'Streaming VM export/import to target'
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

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->iaas_virtual_machine_id)
            ->firstOrFail();

        $targetStorageVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_storage_volume_id)
            ->firstOrFail();

        $targetIp = explode('/', $target->ip_addr)[0];

        $importCmd = 'xe vm-import filename=/dev/stdin'
            . ' sr-uuid=' . escapeshellarg($targetStorageVolume->hypervisor_uuid)
            . ' --preserve';

        $pipeCmd = 'xe vm-export uuid=' . escapeshellarg($vm->hypervisor_uuid) . ' filename=/dev/stdout'
            . ' | ssh -o StrictHostKeyChecking=no -o BatchMode=yes ' . escapeshellarg('root@' . $targetIp)
            . ' ' . escapeshellarg($importCmd);

        if ($isDryRun) {
            $options['dry_run_commands'] = [[
                'host'    => $source->name,
                'command' => $pipeCmd,
                'note'    => 'Stream-export VM "' . $vm->name . '" → import on ' . $target->name
                    . ' (SR ' . $targetStorageVolume->name . ')',
            ]];

            $migration->updateQuietly([
                'options'      => json_encode($options),
                'step_message' => 'Dry-run complete — export/import command listed in options.dry_run_commands',
            ]);

            Log::info(__METHOD__ . ' | Dry-run: export/import command listed, nothing executed.');

            return;
        }

        unset($options['dry_run'], $options['dry_run_commands']);
        $migration->updateQuietly(['options' => json_encode($options)]);

        Log::info(__METHOD__ . ' | Starting export/import stream: ' . $source->name . ' → ' . $target->name
            . ' (SR ' . $targetStorageVolume->hypervisor_uuid . ')');

        $result = self::performCommand($pipeCmd, $source);

        $outputLines = preg_split('/\r\n|\r|\n/', trim($result['output'] ?? ''));
        $newVmUuid   = trim((string) end($outputLines));

        if (!preg_match('/^[0-9a-f-]{36}$/i', $newVmUuid)) {
            throw new \Exception(
                'vm-export/vm-import did not return a valid VM UUID. Output: ' . trim($result['output'] ?? '')
                . (!empty($result['error']) ? ' | Error: ' . trim($result['error']) : '')
            );
        }

        Log::info(__METHOD__ . ' | Import complete — new VM UUID on target: ' . $newVmUuid);

        $options['target_vm_uuid'] = $newVmUuid;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'copying-vhd', 70, 'VM streamed and imported as ' . $newVmUuid);

        Log::info(__METHOD__ . ' | VM export/import complete for migration: ' . $migration->uuid);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 6 — The VM (and its VBDs/VDIs) already exist on the target after
    //           the export/import stream. VDIs get brand-new UUIDs on import
    //           (xe always allocates fresh object UUIDs for an XVA import), so
    //           match each source disk to its target VDI by VBD userdevice —
    //           the same device_number-based reconciliation XenServer82SshDriver
    //           already uses elsewhere for post-import disk sync — rather than
    //           by UUID.
    // ─────────────────────────────────────────────────────────────────────────

    public function rescanTargetSr(VirtualMachineMigrations $migration): array
    {
        $this->updateStep($migration, 'rescanning-sr', 70, 'Matching imported disks to source metadata');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $targetVmUuid = $options['target_vm_uuid'] ?? null;

        if (empty($targetVmUuid)) {
            throw new \Exception('No target VM UUID found. Run copyVhdFiles before this step.');
        }

        $metadata = $options['source_metadata'] ?? null;

        if (empty($metadata['disks'])) {
            throw new \Exception('No disk metadata found. Run collectSourceMetadata before this step.');
        }

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        $result  = self::performCommand('xe vm-disk-list uuid=' . $targetVmUuid, $target);
        $vmDisks = self::parseVmDiskList($result['output']);

        // Index imported VBDs by userdevice — the DB's device_number counterpart.
        $targetByDevice = [];

        foreach ($vmDisks as $vmDisk) {
            $userDevice = trim($vmDisk['vbd']['userdevice'] ?? '');
            $vdiUuid    = trim($vmDisk['vdi']['uuid'] ?? '');

            if ($userDevice === '' || $vdiUuid === '') {
                continue;
            }

            $targetByDevice[$userDevice] = $vdiUuid;
        }

        Log::info(__METHOD__ . ' | Found ' . count($targetByDevice) . ' VBD(s) on imported VM: ' . $targetVmUuid);

        $vdiUuidMap = [];
        $unmatched  = [];

        foreach ($metadata['disks'] as $disk) {
            $sourceVdiUuid = $disk['vdi_uuid'];
            $userDevice    = trim($disk['vbd_userdevice'] ?? '');

            if ($userDevice !== '' && isset($targetByDevice[$userDevice])) {
                $vdiUuidMap[$sourceVdiUuid] = $targetByDevice[$userDevice];
                Log::info(__METHOD__ . ' | Matched disk (device ' . $userDevice . '): '
                    . $sourceVdiUuid . ' → ' . $targetByDevice[$userDevice]);
            } else {
                $unmatched[] = $sourceVdiUuid;
                Log::warning(__METHOD__ . ' | No imported VBD found for source VDI ' . $sourceVdiUuid
                    . ' (expected device ' . $userDevice . ')');
            }
        }

        if (!empty($unmatched)) {
            throw new \Exception(
                'The following source disk(s) could not be matched to an imported VBD by device number: '
                . implode(', ', $unmatched) . '. Check the imported VM\'s disk layout on the target host.'
            );
        }

        $options['vdi_uuid_map'] = $vdiUuidMap;

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'rescanning-sr', 80,
            'Matched ' . count($vdiUuidMap) . ' imported disk(s) to source metadata');

        Log::info(__METHOD__ . ' | VDI map: ' . json_encode($vdiUuidMap));

        return $vdiUuidMap;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 7 — The VM, VBDs, and VDIs already exist on the target after the
    //           export/import stream (vCPUs, memory, and platform params are
    //           carried over from the XVA by xe itself — no need to rebuild
    //           them). The only gap is networking: imported VIFs reference the
    //           SOURCE network UUIDs, which usually don't exist on a different
    //           host/pool, so destroy them and recreate each one pointed at
    //           the correct target network via the existing
    //           resolveTargetNetworkUuid() VLAN-matching logic.
    // ─────────────────────────────────────────────────────────────────────────

    public function recreateVmOnTarget(VirtualMachineMigrations $migration, array $vdiUuidMap): string
    {
        $this->updateStep($migration, 'recreating-vm', 80, 'Reconciling network interfaces on target VM');

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        $targetVmUuid = $options['target_vm_uuid'] ?? null;

        if (empty($targetVmUuid)) {
            throw new \Exception('No target VM UUID found. Run copyVhdFiles before this step.');
        }

        $metadata = $options['source_metadata'] ?? null;

        if (empty($metadata)) {
            throw new \Exception('No source metadata found. Run collectSourceMetadata before this step.');
        }

        $isDryRun = !empty($options['dry_run_recreate']);

        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->source_iaas_compute_member_id)
            ->firstOrFail();

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $migration->target_iaas_compute_member_id)
            ->firstOrFail();

        // ── Destroy whatever VIFs the import created — their network UUIDs are
        //    carried over from the source pool and generally won't resolve on
        //    the target, so they're unreliable at best. ─────────────────────
        $result       = self::performCommand('xe vif-list vm-uuid=' . $targetVmUuid . ' params=uuid', $target);
        $existingVifs = array_values(array_filter(
            AbstractXenService::parseListResult($result['output']),
            fn($v) => !empty($v['uuid'])
        ));

        $commands = [];

        foreach ($existingVifs as $vif) {
            $commands[] = [
                'note' => 'Destroy imported VIF ' . trim($vif['uuid']),
                'cmd'  => 'xe vif-destroy uuid=' . trim($vif['uuid']),
            ];
        }

        foreach ($metadata['nics'] as $nic) {
            $targetNetworkUuid = $this->resolveTargetNetworkUuid($nic, $source, $target);

            $commands[] = [
                'note' => 'Create VIF device=' . $nic['device']
                    . (($nic['vlan'] ?? null) !== null ? ' (VLAN ' . $nic['vlan'] . ')' : ''),
                'cmd'  => 'xe vif-create vm-uuid=' . $targetVmUuid
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
            Log::info(__METHOD__ . ' | Dry-run: ' . count($commands) . ' VIF reconciliation command(s) listed.');
            return $targetVmUuid;
        }

        foreach ($existingVifs as $vif) {
            self::performCommand('xe vif-destroy uuid=' . trim($vif['uuid']), $target);
            Log::info(__METHOD__ . ' | Destroyed imported VIF: ' . trim($vif['uuid']));
        }

        foreach ($metadata['nics'] as $nic) {
            $targetNetworkUuid = $this->resolveTargetNetworkUuid($nic, $source, $target);

            Log::info(__METHOD__ . ' | Creating VIF device=' . $nic['device'] . ' with network-uuid=' . $targetNetworkUuid);

            $result = self::performCommand(
                'xe vif-create vm-uuid=' . $targetVmUuid
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

        $migration->updateQuietly(['options' => json_encode($options)]);

        $this->updateStep($migration, 'recreating-vm', 90, 'Network interfaces reconciled on target: ' . $targetVmUuid);

        Log::info(__METHOD__ . ' | VIF reconciliation complete for target VM: ' . $targetVmUuid);

        return $targetVmUuid;
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

        $vm->updateQuietly(['is_locked' => false]);

        dispatch(new \NextDeveloper\IAAS\Actions\VirtualMachines\Start($vm));

        $vm->updateQuietly(['is_locked' => true]);

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

    /**
     * Resolve the XenServer network UUID on the target host for a given NIC.
     *
     * Strategy:
     *  1. VLAN-based: xe pif-list VLAN=<vlan> → xe network-list PIF-uuids=<pif> --minimal
     *  2. If VLAN=0/null (untagged): xe network-list bridge=xenbr0 --minimal
     *  3. If not found: create the network via createNetwork using the source Networks DB record
     */
    private function resolveTargetNetworkUuid(array $nic, ComputeMembers $source, ComputeMembers $target): string
    {
        $srcNetworkUuid = $nic['network_uuid'] ?? null;
        $vlan           = $nic['vlan'] ?? null;
        $networkName    = null;

        // ── Step 1: resolve vlan/name from source CMNI ───────────────────────
        if ($srcNetworkUuid) {
            $srcCmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $source->id)
                ->where('network_uuid', $srcNetworkUuid)
                ->first();

            if ($srcCmni) {
                $vlan        = $srcCmni->vlan ?: $vlan;
                $networkName = $srcCmni->network_name;
            }
        }

        // ── Step 2: find matching CMNI on target by VLAN ─────────────────────
        if ($vlan > 0) {
            $tgtCmni = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $target->id)
                ->where('vlan', $vlan)
                ->first();

            if ($tgtCmni?->network_uuid) {
                return $tgtCmni->network_uuid;
            }
        }

        // ── Step 3: live query on target via xe pif-list VLAN= ───────────────
        if ($vlan > 0) {
            $pifResult = self::performCommand(
                'xe pif-list VLAN=' . (int) $vlan . ' --minimal',
                $target
            );
            $pifUuid = trim($pifResult['output'] ?? '');

            if ($pifUuid) {
                $netResult = self::performCommand(
                    'xe network-list PIF-uuids=' . escapeshellarg($pifUuid) . ' --minimal',
                    $target
                );
                $uuid = trim($netResult['output'] ?? '');
                if ($uuid) {
                    return $uuid;
                }
            }
            // VLAN known but not on target → go to createNetwork (skip bridge fallback)
        }

        // ── Step 4: VLAN unknown — bridge fallback for untagged/mgmt NICs ────
        if (!($vlan > 0)) {
            $result = self::performCommand(
                'xe network-list bridge=' . escapeshellarg('xenbr' . ($nic['device'] ?? '0')) . ' --minimal',
                $target
            );
            $uuid = trim($result['output'] ?? '');
            if ($uuid) {
                return $uuid;
            }
        }

        // ── Step 5: create the network on the target ─────────────────────────
        $networkModel = null;

        if ($networkName) {
            $networkModel = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('name', $networkName)
                ->first();
        }

        if (!$networkModel && $vlan > 0) {
            $networkModel = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $vlan)
                ->first();
        }

        if (!$networkModel) {
            throw new \Exception(
                'Cannot resolve target network for NIC device=' . ($nic['device'] ?? '?')
                . ' (source network-uuid: ' . ($srcNetworkUuid ?? 'unknown')
                . ', vlan: ' . ($vlan ?? 'none') . ', name: ' . ($networkName ?? 'unknown') . ').'
            );
        }

        Log::info(__METHOD__ . ' | Creating network on target: ' . $networkModel->name . ' (VLAN ' . $vlan . ')');
        $newCmni = ComputeMemberXenService::createNetwork($target, $networkModel);

        return $newCmni->network_uuid;
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
