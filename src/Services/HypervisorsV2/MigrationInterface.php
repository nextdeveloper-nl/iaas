<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2;

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
     * STEP 5 — Copy each VDI's VHD file from the source NFS SR to the target NFS SR.
     * Preferred: rsync with --checksum --progress over SSH.
     * Fallback: dd over SSH pipe.
     * Verifies file integrity (size or checksum) after each transfer.
     *
     * @throws \Exception on transfer or integrity failure
     */
    public function copyVhdFiles(VirtualMachineMigrations $migration): void;

    /**
     * STEP 6 — Trigger `xe sr-scan` on the target host, then query the SR to find
     * newly detected VDIs. Returns a map of original VDI UUID => new VDI UUID on target.
     *
     * @return array<string, string> [ source_vdi_uuid => target_vdi_uuid ]
     */
    public function rescanTargetSr(VirtualMachineMigrations $migration): array;

    /**
     * STEP 7 — Recreate the VM record on the target host using the metadata collected
     * in Step 2: VM record, vCPU/memory params, HVM/platform params, VBDs (with the
     * new VDI UUIDs from Step 6), and VIFs (using operator-supplied network UUID mapping,
     * preserving original MAC addresses). VM is NOT started.
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
