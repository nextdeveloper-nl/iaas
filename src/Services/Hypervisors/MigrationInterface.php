<?php

namespace NextDeveloper\IAAS\Services\Hypervisors;

use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;

interface MigrationInterface
{
    /**
     * STEP 1 — Verify SSH connectivity to both hosts, confirm the source VM exists
     * and is in a known state, confirm the target SR exists with sufficient free space,
     * and confirm NFS mounts are accessible on both sides.
     *
     * @throws \Exception on any pre-flight failure
     */
    public function preFlightChecks(VirtualMachineMigrations $migration): void;

    /**
     * STEP 2 — Collect and return all VM metadata from the source host:
     * VM record, vCPU/memory settings, HVM boot params, platform params,
     * all VBDs + VDIs (with VHD paths), all VIFs (with MAC addresses), PV-args.
     *
     * @return array structured metadata for use in later steps
     */
    public function collectSourceMetadata(VirtualMachineMigrations $migration): array;

    /**
     * STEP 3 — Check for snapshots on the source VM. If snapshots exist, either
     * abort or await operator confirmation before proceeding. After snapshot cleanup,
     * trigger SR scan/coalesce and verify the final VHD is a single flat file.
     *
     * @throws \Exception if snapshots exist and cannot be resolved
     */
    public function validateAndCoalesceVhd(VirtualMachineMigrations $migration): void;

    /**
     * STEP 4 — Gracefully shut down the source VM. Poll power-state until halted
     * (timeout: 5 minutes). Falls back to forced shutdown if needed.
     *
     * @throws \Exception if the VM cannot be halted
     */
    public function shutdownSourceVm(VirtualMachineMigrations $migration): void;

    /**
     * STEP 5 — Get the VM's disk data onto the target host. Strategy is
     * implementation-specific: MigrationService rsyncs each VDI's VHD file from
     * the source NFS SR to the target NFS SR; LocalDiskMigrationService streams
     * `xe vm-export | ssh ... xe vm-import` so xe itself imports the VM (disks
     * and metadata) directly onto the target's local SR — this also produces
     * the target VM UUID that later steps use, since a live import can't be
     * matched back to the source by VDI UUID (see Step 6).
     *
     * @throws \Exception on transfer or integrity failure
     */
    public function copyVhdFiles(VirtualMachineMigrations $migration): void;

    /**
     * STEP 6 — Reconcile disk identity between source and target. Returns a map
     * of original VDI UUID => new VDI UUID on target. Strategy is
     * implementation-specific: MigrationService triggers `xe sr-scan` on the
     * target SR since its rsync copy preserves VDI UUIDs, so the source UUID is
     * still present after the scan; LocalDiskMigrationService's target VM
     * already exists (from Step 5's vm-import, which always allocates fresh
     * VDI UUIDs), so it matches disks by VBD device number instead.
     *
     * @return array<string, string> [ source_vdi_uuid => target_vdi_uuid ]
     */
    public function rescanTargetSr(VirtualMachineMigrations $migration): array;

    /**
     * STEP 7 — Get the VM into its final, correctly-networked state on the
     * target host. VM is NOT started. Strategy is implementation-specific:
     * MigrationService builds the VM record from scratch (VM record,
     * vCPU/memory/platform params, VBDs using the Step 6 VDI map, VIFs);
     * LocalDiskMigrationService's VM already exists post-import with correct
     * vCPU/memory/platform/VBDs, so it only needs to destroy the imported VIFs
     * (whose network UUIDs reference the source pool) and recreate them
     * against the correct target network, preserving original MAC addresses.
     *
     * @return string the new VM UUID on the target host
     */
    public function recreateVmOnTarget(VirtualMachineMigrations $migration, array $vdiUuidMap): string;

    /**
     * STEP 8 — Verify the recreated VM record is complete: correct vCPU count,
     * memory settings, all disks and NICs present. Returns a structured validation summary.
     *
     * @return array validation summary
     */
    public function postMigrationValidation(VirtualMachineMigrations $migration): array;

    /**
     * STEP 9 — Sync the database records to reflect the completed migration:
     * VirtualMachines (new hypervisor_uuid + compute member), VirtualDiskImages
     * (new VDI UUID + target storage volume), VirtualNetworkCards (target network).
     * VM status is set to halted; startVmOnTarget will set it to running.
     */
    public function syncDatabaseRecords(VirtualMachineMigrations $migration): void;

    /**
     * STEP 10 — Start the VM on the target host. Poll power-state until running
     * (timeout: 3 minutes). Updates the migration record and VM status on success.
     *
     * @throws \Exception if the VM does not reach running state within the timeout
     */
    public function startVmOnTarget(VirtualMachineMigrations $migration): void;

    /**
     * Orchestrates all 9 steps in order. Updates migration record progress at each step.
     * Marks migration as failed with an error message if any step throws.
     */
    public function run(VirtualMachineMigrations $migration): void;
}
