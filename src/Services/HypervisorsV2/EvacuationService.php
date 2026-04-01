<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\VirtualMachineMigrationsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class EvacuationService
{
    /**
     * Proposes an evacuation plan for moving the given VM to the target compute member.
     *
     * Inspects every disk and NIC attached to the VM, finds compatible storage volumes
     * and networks on the target host, and returns a structured plan. The plan can be
     * reviewed by the operator before a VirtualMachineMigrations record is created and
     * the actual migration is started.
     *
     * Return structure:
     * [
     *   'is_feasible'           => bool,
     *   'warnings'              => string[],
     *   'vm'                    => [...],
     *   'source_compute_member' => [...],
     *   'target_compute_member' => [...],
     *   'total_disk_size'       => int (bytes),
     *   'storage_mapping'       => [ [ 'disk', 'source_storage_volume', 'target_storage_volume', 'match_confidence' ], ... ],
     *   'network_mapping'       => [ [ 'nic', 'source_network', 'target_network', 'match_confidence' ], ... ],
     * ]
     *
     * Supported $options keys:
     *   'preferred_storage_type' => string        — apply this disk_physical_type preference to all disks
     *                                               e.g. 'nfs', 'lvm', 'ext'
     *   'disk_storage_type'      => array<uuid,string> — per-disk type override, keyed by disk UUID
     *                                               e.g. [ 'abc-123' => 'nfs', 'def-456' => 'lvm' ]
     */
    public static function proposePlan(VirtualMachines $vm, ComputeMembers $target, array $options = []): array
    {
        $warnings   = [];
        $isFeasible = true;

        // ── Source compute member ─────────────────────────────────────────────
        $source = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        // ── Disks attached to the VM (CDROMs excluded) ────────────────────────
        $disks = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('is_cdrom', false)
            ->whereNull('deleted_at')
            ->get();

        // ── NICs attached to the VM ───────────────────────────────────────────
        $nics = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->whereNull('deleted_at')
            ->get();

        // ── Storage volumes available on the target ───────────────────────────
        $targetCmVolumes = ComputeMemberStorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $target->id)
            ->whereNull('deleted_at')
            ->get();

        $targetVolumeIds = $targetCmVolumes->pluck('iaas_storage_volume_id');

        $targetVolumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->whereIn('id', $targetVolumeIds)
            ->where('is_storage', true)
            ->whereNull('deleted_at')
            ->get();

        // ── Networks available on the target's cloud node ─────────────────────
        $targetCloudNode  = ComputeMembersService::getCloudNode($target);
        $targetCloudNodeId = $targetCloudNode?->id;

        $targetNetworks = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $targetCloudNodeId)
            ->whereNull('deleted_at')
            ->get();

        // ── RAM feasibility check ─────────────────────────────────────────────
        // VirtualMachines->ram is in MB; ComputeMembers->free_ram / total_ram are in GB.
        $vmRamGb        = $vm->ram / 1024;
        $targetFreeRamGb = $target->free_ram;

        if ($targetFreeRamGb !== null && $vmRamGb > $targetFreeRamGb) {
            $isFeasible = false;
            $warnings[] = 'Target host does not have enough free RAM. '
                . 'Required: ' . $vmRamGb . ' GB (' . $vm->ram . ' MB), available: ' . $targetFreeRamGb . ' GB.';
        }

        // ── Build storage mapping ─────────────────────────────────────────────
        $storageMapping = [];
        $totalDiskSize  = 0;

        $preferredStorageType  = $options['preferred_storage_type'] ?? null;
        $perDiskStorageTypes   = $options['disk_storage_type'] ?? [];

        foreach ($disks as $disk) {
            $totalDiskSize += (int) $disk->size;

            $sourceVolume = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $disk->iaas_storage_volume_id)
                ->first();

            // Determine the effective type preference for this disk:
            //   1. per-disk override takes highest priority
            //   2. global preferred_storage_type option
            //   3. fall back to matching the source volume type
            $effectiveType = $perDiskStorageTypes[$disk->uuid]
                ?? $preferredStorageType
                ?? $sourceVolume?->disk_physical_type;

            // VirtualDiskImages->size is in bytes; StorageVolumes->free_hdd is in GB.
            $diskSizeGb = ceil($disk->size / 1000 / 1000 / 1000);

            // Match by effective type + enough free space
            $matched = $targetVolumes
                ->filter(fn($v) => $v->disk_physical_type === $effectiveType
                    && $v->free_hdd >= $diskSizeGb)
                ->first();

            // Fallback: any target volume with enough free space regardless of type.
            // No warning is issued here — match_confidence = 'compatible' in the mapping
            // already communicates that the type preference was not exactly satisfied.
            if (!$matched) {
                $matched = $targetVolumes
                    ->filter(fn($v) => $v->free_hdd >= $diskSizeGb)
                    ->first();
            }

            if (!$matched) {
                $isFeasible = false;
                $warnings[] = 'No suitable storage volume on target for disk "' . $disk->name . '"'
                    . ' (size: ' . $diskSizeGb . ' GB'
                    . ', type: ' . ($effectiveType ?? 'unknown') . ').';
            }

            $storageMapping[] = [
                'disk' => [
                    'id'            => $disk->id,
                    'uuid'          => $disk->uuid,
                    'name'          => $disk->name,
                    'size_bytes'    => $disk->size,
                    'size_gb'       => $diskSizeGb,
                    'device_number' => $disk->device_number,
                ],
                'requested_storage_type'  => $effectiveType,
                'source_storage_volume' => $sourceVolume ? [
                    'id'                 => $sourceVolume->id,
                    'uuid'               => $sourceVolume->uuid,
                    'name'               => $sourceVolume->name,
                    'disk_physical_type' => $sourceVolume->disk_physical_type,
                    'free_hdd'           => $sourceVolume->free_hdd,
                ] : null,
                'target_storage_volume' => $matched ? [
                    'id'                 => $matched->id,
                    'uuid'               => $matched->uuid,
                    'name'               => $matched->name,
                    'disk_physical_type' => $matched->disk_physical_type,
                    'free_hdd'           => $matched->free_hdd,
                ] : null,
                'match_confidence' => self::storageMatchConfidence($sourceVolume, $matched),
            ];
        }

        // ── Build network mapping ─────────────────────────────────────────────
        $networkMapping = [];

        foreach ($nics as $nic) {
            $sourceNetwork = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $nic->iaas_network_id)
                ->first();

            // Match by VLAN first (most reliable), then fall back to name
            $matchedNetwork = null;

            if ($sourceNetwork?->vlan) {
                $matchedNetwork = $targetNetworks
                    ->where('vlan', $sourceNetwork->vlan)
                    ->first();
            }

            if (!$matchedNetwork && $sourceNetwork?->name) {
                $matchedNetwork = $targetNetworks
                    ->where('name', $sourceNetwork->name)
                    ->first();
            }

            if (!$matchedNetwork) {
                // No existing network found on the target — the migration will auto-create it
                // using ComputeMemberXenService::createNetwork() during the recreate-vm step.
                // This is reflected as match_confidence = 'will_create' in the mapping below.
                $warnings[] = 'No existing network found on target for NIC "' . $nic->name . '"'
                    . ' (source: "' . ($sourceNetwork?->name ?? 'unknown') . '"'
                    . ', VLAN: ' . ($sourceNetwork?->vlan ?? 'none') . ')'
                    . ' — it will be created automatically on the target host during migration.';
            }

            $networkMapping[] = [
                'nic' => [
                    'id'            => $nic->id,
                    'uuid'          => $nic->uuid,
                    'name'          => $nic->name,
                    'mac_addr'      => $nic->mac_addr,
                    'device_number' => $nic->device_number,
                ],
                'source_network' => $sourceNetwork ? [
                    'id'   => $sourceNetwork->id,
                    'uuid' => $sourceNetwork->uuid,
                    'name' => $sourceNetwork->name,
                    'vlan' => $sourceNetwork->vlan,
                ] : null,
                'target_network' => $matchedNetwork ? [
                    'id'   => $matchedNetwork->id,
                    'uuid' => $matchedNetwork->uuid,
                    'name' => $matchedNetwork->name,
                    'vlan' => $matchedNetwork->vlan,
                ] : null,
                'match_confidence' => self::networkMatchConfidence($sourceNetwork, $matchedNetwork),
            ];
        }

        return [
            'is_feasible'           => $isFeasible,
            'warnings'              => $warnings,
            'vm'                    => [
                'id'         => $vm->id,
                'uuid'       => $vm->uuid,
                'name'       => $vm->name,
                'status'     => $vm->status,
                'cpu'        => $vm->cpu,
                'ram_mb'     => $vm->ram,
                'ram_gb'     => round($vm->ram / 1024, 2),
            ],
            'source_compute_member' => [
                'id'               => $source->id,
                'uuid'             => $source->uuid,
                'name'             => $source->name,
                'hypervisor_model' => $source->hypervisor_model,
            ],
            'target_compute_member' => [
                'id'               => $target->id,
                'uuid'             => $target->uuid,
                'name'             => $target->name,
                'hypervisor_model' => $target->hypervisor_model,
                'free_ram_gb'      => $target->free_ram,
                'total_ram_gb'     => $target->total_ram,
            ],
            'total_disk_size'       => $totalDiskSize,
            'storage_mapping'       => $storageMapping,
            'network_mapping'       => $networkMapping,
        ];
    }

    /**
     * Approves a proposed evacuation plan and creates a VirtualMachineMigrations record.
     *
     * The plan must have `is_feasible = true`. The full plan (including storage and network
     * mappings) is stored in the `options` column so the MigrationService can use it during
     * execution without re-querying.
     *
     * The first disk's source/target storage volume is used as the primary volume reference
     * on the migration record. All disk mappings are preserved in `options`.
     *
     * @param  array $plan  The array returned by proposePlan()
     * @return \NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations
     * @throws \InvalidArgumentException if the plan is not feasible
     */
    public static function approvePlan(array $plan): \NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations
    {
        if (!$plan['is_feasible']) {
            throw new \InvalidArgumentException(
                'Cannot approve a plan that is not feasible. Warnings: '
                . implode(' | ', $plan['warnings'])
            );
        }

        // Resolve primary source/target storage volumes from the first disk mapping
        $firstStorageMapping     = $plan['storage_mapping'][0] ?? null;
        $sourceStorageVolumeId   = $firstStorageMapping['source_storage_volume']['id'] ?? null;
        $targetStorageVolumeId   = $firstStorageMapping['target_storage_volume']['id'] ?? null;

        // Resolve storage members from the storage volumes
        $sourceStorageMemberId = null;
        $targetStorageMemberId = null;

        if ($sourceStorageVolumeId) {
            $sourceStorageMemberId = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $sourceStorageVolumeId)
                ->value('iaas_storage_member_id');
        }

        if ($targetStorageVolumeId) {
            $targetStorageMemberId = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $targetStorageVolumeId)
                ->value('iaas_storage_member_id');
        }

        $migration = VirtualMachineMigrationsService::create([
            'iaas_virtual_machine_id'        => $plan['vm']['id'],
            'source_iaas_compute_member_id'  => $plan['source_compute_member']['id'],
            'target_iaas_compute_member_id'  => $plan['target_compute_member']['id'],
            'source_iaas_storage_volume_id'  => $sourceStorageVolumeId,
            'target_iaas_storage_volume_id'  => $targetStorageVolumeId,
            'source_iaas_storage_member_id'  => $sourceStorageMemberId,
            'target_iaas_storage_member_id'  => $targetStorageMemberId,
            'status'                         => 'pending',
            'current_step'                   => null,
            'progress'                       => 0,
            'options'                        => json_encode($plan),
            'iam_account_id'                 => UserHelper::currentAccount()?->id,
            'iam_user_id'                    => UserHelper::me()?->id,
        ]);

        return $migration;
    }

    /**
     * Returns the confidence level of a storage volume match.
     * 'exact'      — same physical type and enough space
     * 'compatible' — different type but enough space
     * 'none'       — no suitable volume found
     */
    private static function storageMatchConfidence(?StorageVolumes $source, ?StorageVolumes $target): string
    {
        if (!$target) {
            return 'none';
        }

        if ($source && $source->disk_physical_type === $target->disk_physical_type) {
            return 'exact';
        }

        return 'compatible';
    }

    /**
     * Returns the confidence level of a network match.
     * 'exact'       — matched by VLAN on the target cloud node
     * 'name'        — matched by network name only
     * 'will_create' — no existing match; network will be auto-created on the target host during migration
     */
    private static function networkMatchConfidence(?Networks $source, ?Networks $target): string
    {
        if (!$target) {
            return 'will_create';
        }

        if ($source && $source->vlan && $source->vlan === $target->vlan) {
            return 'exact';
        }

        return 'name';
    }
}
