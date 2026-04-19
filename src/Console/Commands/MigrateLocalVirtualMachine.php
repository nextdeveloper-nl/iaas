<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\HypervisorsV2\EvacuationService;
use NextDeveloper\IAAS\Services\HypervisorsV2\XenServer_8_2\LocalDiskMigrationService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Step-by-step local-disk VM migration command.
 *
 * Migrates VMs whose disks reside on local (EXT) SRs by rsyncing VHD files
 * directly between hypervisors over SSH — no NFS storage member required.
 *
 * Usage examples:
 *
 *   # 1. Propose an evacuation plan (dry-run, no DB changes)
 *   php artisan leo:migrate-local-vm --step=propose --vm-id=<vm-uuid> --target-id=<compute-member-uuid>
 *
 *   # 2. Approve the plan and create a migration record
 *   php artisan leo:migrate-local-vm --step=approve --vm-id=<vm-uuid> --target-id=<cm-uuid>
 *
 *   # 3-12. Run individual migration steps (use the migration UUID printed by approve)
 *   php artisan leo:migrate-local-vm --step=pre-flight       --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=collect-metadata --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=coalesce-vhd     --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=shutdown         --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=copy-vhd         --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=rescan-sr        --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=recreate-vm      --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=validate         --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=sync-db          --migration-id=<uuid>
 *   php artisan leo:migrate-local-vm --step=start-vm         --migration-id=<uuid>
 *
 *   # Run the full migration end-to-end
 *   php artisan leo:migrate-local-vm --step=run --migration-id=<uuid>
 */
class MigrateLocalVirtualMachine extends Command
{
    protected $signature = 'leo:migrate-local-vm
        {--step= : Step to run: propose, approve, pre-flight, collect-metadata, coalesce-vhd, shutdown, copy-vhd, rescan-sr, recreate-vm, validate, sync-db, start-vm, run}
        {--vm-id= : UUID of the VirtualMachine (required for propose and approve)}
        {--target-id= : UUID of the target ComputeMember (required for propose and approve)}
        {--migration-id= : UUID of an existing VirtualMachineMigrations record (required for steps 3-12)}
        {--storage-type= : Preferred storage type for propose step (e.g. local, lvm, nfs)}
        {--force-delete-snapshots : Allow deletion of VM snapshots during coalesce-vhd step}
        {--dry-run : For copy-vhd and recreate-vm: resolve and print all commands without executing them}';

    protected $description = 'Run local-disk VM migration steps one by one (direct hypervisor-to-hypervisor rsync over SSH)';

    public function handle(): int
    {
        UserHelper::setAdminAsCurrentUser();

        $step = $this->option('step');

        if (empty($step)) {
            $this->error('No --step provided. Available steps:');
            $this->printSteps();
            return self::FAILURE;
        }

        try {
            match ($step) {
                'propose'          => $this->stepPropose(),
                'approve'          => $this->stepApprove(),
                'pre-flight'       => $this->stepPreFlight(),
                'collect-metadata' => $this->stepCollectMetadata(),
                'coalesce-vhd'     => $this->stepCoalesceVhd(),
                'shutdown'         => $this->stepShutdown(),
                'copy-vhd'         => $this->stepCopyVhd(),
                'rescan-sr'        => $this->stepRescanSr(),
                'recreate-vm'      => $this->stepRecreateVm(),
                'validate'         => $this->stepValidate(),
                'sync-db'          => $this->stepSyncDb(),
                'start-vm'         => $this->stepStartVm(),
                'run'              => $this->stepRun(),
                default            => $this->unknownStep($step),
            };
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Step failed: ' . $e->getMessage());
            $this->line('<fg=gray>' . $e->getFile() . ':' . $e->getLine() . '</>');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEPS
    // ─────────────────────────────────────────────────────────────────────────

    private function stepPropose(): void
    {
        [$vm, $target] = $this->resolveVmAndTarget();

        $options = $this->buildStorageOptions($vm);
        if ($type = $this->option('storage-type')) {
            $options['preferred_storage_type'] = $type;
        }

        $this->info('Proposing evacuation plan...');
        $this->newLine();

        $plan = EvacuationService::proposePlan($vm, $target, $options);

        $this->printPlan($plan);
    }

    private function stepApprove(): void
    {
        [$vm, $target] = $this->resolveVmAndTarget();

        $options = $this->buildStorageOptions($vm);
        if ($type = $this->option('storage-type')) {
            $options['preferred_storage_type'] = $type;
        }

        $this->info('Proposing plan...');
        $plan = EvacuationService::proposePlan($vm, $target, $options);

        $this->newLine();
        $this->printPlan($plan);
        $this->newLine();

        if (!$plan['is_feasible']) {
            $this->error('Plan is not feasible. Fix the warnings above before approving.');
            return;
        }

        if (!$this->confirm('Approve this plan and create a migration record?', true)) {
            $this->line('Aborted.');
            return;
        }

        $migration = EvacuationService::approvePlan($plan);

        $this->newLine();
        $this->info('Migration record created.');
        $this->printMigrationSummary($migration);
        $this->newLine();
        $this->line('Next step:');
        $this->line('  php artisan leo:migrate-local-vm --step=pre-flight --migration-id=' . $migration->uuid);
    }

    private function stepPreFlight(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 1] Running pre-flight checks (local disk)...');
        $service->preFlightChecks($migration);

        $this->info('Pre-flight checks passed.');
        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('collect-metadata', $migration->uuid);
    }

    private function stepCollectMetadata(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 2] Collecting source VM metadata...');
        $metadata = $service->collectSourceMetadata($migration);

        $this->info('Metadata collected.');
        $this->newLine();

        $vm = $metadata['vm'];
        $this->table(['Field', 'Value'], [
            ['Name',   $vm['name_label']],
            ['UUID',   $vm['uuid']],
            ['vCPUs',  $vm['vcpus_max']],
            ['Memory', round($vm['memory_static_max'] / 1024 / 1024 / 1024, 2) . ' GB'],
            ['Disks',  count($metadata['disks'])],
            ['NICs',   count($metadata['nics'])],
        ]);

        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('coalesce-vhd', $migration->uuid);
    }

    private function stepCoalesceVhd(): void
    {
        $migration = $this->resolveMigration();

        if ($this->option('force-delete-snapshots')) {
            $options = is_array($migration->options)
                ? $migration->options
                : (json_decode($migration->options, true) ?? []);

            $options['force_delete_snapshots'] = true;

            $migration->updateQuietly(['options' => json_encode($options)]);
            $this->line('  force_delete_snapshots enabled.');
        }

        $service = new LocalDiskMigrationService();

        $this->info('[Step 3] Validating VHD chain and waiting for coalesce...');
        $service->validateAndCoalesceVhd($migration);

        $this->info('VHD coalesce complete.');
        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('shutdown', $migration->uuid);
    }

    private function stepShutdown(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 4] Shutting down source VM...');
        $service->shutdownSourceVm($migration);

        $this->info('VM halted.');
        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('copy-vhd', $migration->uuid);
    }

    private function stepCopyVhd(): void
    {
        $migration = $this->resolveMigration();

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        if ($this->option('dry-run')) {
            $options['dry_run'] = true;
            $this->line('  <fg=yellow>Dry-run mode enabled — no SSH commands will be executed.</>');
        } else {
            unset($options['dry_run'], $options['dry_run_commands']);
        }

        $migration->updateQuietly(['options' => json_encode($options)]);

        $service = new LocalDiskMigrationService();

        $this->info('[Step 5] ' . ($this->option('dry-run') ? 'Resolving' : 'Copying') . ' VHD files (direct rsync over SSH)...');
        $service->copyVhdFiles($migration);

        $fresh   = $migration->fresh();
        $options = is_array($fresh->options) ? $fresh->options : (json_decode($fresh->options, true) ?? []);

        if (!empty($options['dry_run']) && !empty($options['dry_run_commands'])) {
            $this->newLine();
            $this->line('Commands that <fg=yellow>would</> be executed on <fg=cyan>'
                . ($options['dry_run_commands'][0]['host'] ?? 'source host') . '</>:');
            $this->newLine();

            foreach ($options['dry_run_commands'] as $i => $cmd) {
                $this->line(sprintf('<fg=gray>[%d]</> <fg=yellow>%s</>', $i + 1, $cmd['note']));
                $this->line('    ' . $cmd['command']);
                $this->newLine();
            }

            $this->line('To execute for real, remove <fg=yellow>dry_run</> from migration options and re-run this step.');
            return;
        }

        $this->info('VHD copy complete.');
        $this->printMigrationSummary($fresh);
        $this->nextStepHint('rescan-sr', $migration->uuid);
    }

    private function stepRescanSr(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 6] Rescanning target SR and detecting copied VDIs...');
        $vdiUuidMap = $service->rescanTargetSr($migration);

        $this->info('SR scan complete. VDI map:');
        foreach ($vdiUuidMap as $source => $target) {
            $this->line("  {$source} → {$target}");
        }

        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('recreate-vm', $migration->uuid);
    }

    private function stepRecreateVm(): void
    {
        $migration = $this->resolveMigration();

        $options = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);

        if ($this->option('dry-run')) {
            $options['dry_run_recreate'] = true;
            $this->line('  <fg=yellow>Dry-run mode enabled — no XenServer commands will be executed.</>');
        } else {
            unset($options['dry_run_recreate'], $options['dry_run_commands_recreate']);
        }

        $migration->updateQuietly(['options' => json_encode($options)]);

        $vdiUuidMap = $options['vdi_uuid_map'] ?? [];
        $service    = new LocalDiskMigrationService();

        $this->info('[Step 7] ' . ($this->option('dry-run') ? 'Resolving' : 'Recreating') . ' VM record on target host...');
        $service->recreateVmOnTarget($migration, $vdiUuidMap);

        $fresh   = $migration->fresh();
        $options = is_array($fresh->options) ? $fresh->options : (json_decode($fresh->options, true) ?? []);

        if (!empty($options['dry_run_recreate']) && !empty($options['dry_run_commands_recreate'])) {
            $this->newLine();
            $this->line('Commands that <fg=yellow>would</> be executed on target host:');
            $this->newLine();

            foreach ($options['dry_run_commands_recreate'] as $i => $entry) {
                $this->line(sprintf('<fg=gray>[%d]</> <fg=yellow>%s</>', $i + 1, $entry['note']));
                $this->line('    ' . $entry['cmd']);
                $this->newLine();
            }

            $this->line('To execute for real, re-run without <fg=yellow>--dry-run</>.');
            return;
        }

        $this->info('VM recreated on target: ' . ($options['target_vm_uuid'] ?? 'unknown'));
        $this->printMigrationSummary($fresh);
        $this->nextStepHint('validate', $migration->uuid);
    }

    private function stepValidate(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 8] Running post-migration validation...');
        $summary = $service->postMigrationValidation($migration);

        $this->newLine();
        $rows = [];
        foreach ($summary['checks'] as $name => $check) {
            $rows[] = [
                ucfirst($name),
                $check['expected'],
                $check['actual'],
                $check['pass'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>',
            ];
        }
        $this->table(['Check', 'Expected', 'Actual', 'Result'], $rows);

        if ($summary['is_valid']) {
            $this->info('Validation passed.');
        } else {
            $this->error('Validation FAILED — review the table above before proceeding.');
        }

        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('sync-db', $migration->uuid);
    }

    private function stepSyncDb(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 9] Syncing database records to target...');
        $service->syncDatabaseRecords($migration);

        $this->info('Database records synced.');
        $this->printMigrationSummary($migration->fresh());
        $this->nextStepHint('start-vm', $migration->uuid);
    }

    private function stepStartVm(): void
    {
        $migration = $this->resolveMigration();
        $service   = new LocalDiskMigrationService();

        $this->info('[Step 10] Starting VM on target host...');
        $service->startVmOnTarget($migration);

        $fresh = $migration->fresh();
        $this->info('VM started. Migration status: ' . $fresh->status);
        $this->printMigrationSummary($fresh);
    }

    private function stepRun(): void
    {
        $migration = $this->resolveMigration();

        if (!$this->confirm(
            'Run the full local-disk migration end-to-end for migration ' . $migration->uuid . '?',
            false
        )) {
            $this->line('Aborted.');
            return;
        }

        $service = new LocalDiskMigrationService();

        $this->info('Starting full local-disk migration...');
        $service->run($migration);

        $fresh = $migration->fresh();
        $this->newLine();
        $this->info('Migration complete. Status: ' . $fresh->status);
        $this->printMigrationSummary($fresh);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESOLVERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Builds disk_storage_type options so EvacuationService maps each local disk
     * to a matching local SR on the target instead of falling back to NFS.
     * NFS extra disks are left unforced so the plan's default matching applies.
     */
    private function buildStorageOptions(VirtualMachines $vm): array
    {
        $disks = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('is_cdrom', false)
            ->whereNull('deleted_at')
            ->get();

        $perDiskTypes = [];

        foreach ($disks as $disk) {
            if (!$disk->iaas_storage_volume_id) {
                continue;
            }

            $sv = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $disk->iaas_storage_volume_id)
                ->first();

            if ($sv && $sv->disk_physical_type !== 'nfs') {
                // Force this local disk to map to a matching local SR on the target.
                $perDiskTypes[$disk->uuid] = $sv->disk_physical_type;
            }
            // NFS extra disks: omit — plan's default type-matching handles them.
        }

        return empty($perDiskTypes) ? [] : ['disk_storage_type' => $perDiskTypes];
    }

    private function resolveVmAndTarget(): array
    {
        $vmId     = $this->option('vm-id');
        $targetId = $this->option('target-id');

        if (empty($vmId) || empty($targetId)) {
            throw new \InvalidArgumentException('Both --vm-id and --target-id are required for this step.');
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $vmId)
            ->whereNull('deleted_at')
            ->first();

        if (!$vm) {
            throw new \InvalidArgumentException('VirtualMachine not found with UUID: ' . $vmId);
        }

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $targetId)
            ->whereNull('deleted_at')
            ->first();

        if (!$target) {
            throw new \InvalidArgumentException('ComputeMember not found with UUID: ' . $targetId);
        }

        return [$vm, $target];
    }

    private function resolveMigration(): VirtualMachineMigrations
    {
        $migrationId = $this->option('migration-id');

        if (empty($migrationId)) {
            throw new \InvalidArgumentException('--migration-id is required for this step.');
        }

        $migration = VirtualMachineMigrations::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $migrationId)
            ->first();

        if (!$migration) {
            throw new \InvalidArgumentException('VirtualMachineMigrations not found with UUID: ' . $migrationId);
        }

        return $migration;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OUTPUT HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function printPlan(array $plan): void
    {
        $feasible = $plan['is_feasible'] ? '<fg=green>YES</>' : '<fg=red>NO</>';
        $this->line('  Feasible : ' . $feasible);
        $this->line('  VM       : ' . $plan['vm']['name'] . ' (' . $plan['vm']['uuid'] . ')');
        $this->line('  Source   : ' . $plan['source_compute_member']['name']);
        $this->line('  Target   : ' . $plan['target_compute_member']['name']
            . '  (free RAM: ' . $plan['target_compute_member']['free_ram_gb'] . ' GB)');
        $this->line('  Total disk: ' . round($plan['total_disk_size'] / 1024 / 1024 / 1024, 2) . ' GB');

        if (!empty($plan['warnings'])) {
            $this->newLine();
            $this->warn('Warnings:');
            foreach ($plan['warnings'] as $w) {
                $this->line('  ! ' . $w);
            }
        }

        $this->newLine();
        $this->line('Storage mapping:');
        foreach ($plan['storage_mapping'] as $m) {
            $target = $m['target_storage_volume']['name'] ?? 'NONE';
            $conf   = $m['match_confidence'];
            $color  = match ($conf) { 'exact' => 'green', 'compatible' => 'yellow', default => 'red' };
            $this->line(sprintf(
                '  Disk %-30s → %-30s [<fg=%s>%s</>]',
                $m['disk']['name'] . ' (' . $m['disk']['size_gb'] . ' GB)',
                $target,
                $color,
                $conf
            ));
        }

        $this->newLine();
        $this->line('Network mapping:');
        foreach ($plan['network_mapping'] as $m) {
            $target = $m['target_network']['name'] ?? 'NONE';
            $conf   = $m['match_confidence'];
            $color  = match ($conf) { 'exact' => 'green', 'name' => 'yellow', 'will_create' => 'cyan', default => 'red' };
            $this->line(sprintf(
                '  NIC  %-30s → %-30s [<fg=%s>%s</>]',
                $m['nic']['name'],
                $target,
                $color,
                $conf
            ));
        }
    }

    private function printMigrationSummary(VirtualMachineMigrations $migration): void
    {
        $this->newLine();
        $this->table(['Field', 'Value'], [
            ['Migration UUID', $migration->uuid],
            ['Status',         $migration->status],
            ['Current step',   $migration->current_step ?? '—'],
            ['Progress',       $migration->progress . '%'],
            ['Message',        $migration->step_message ?? '—'],
        ]);
    }

    private function nextStepHint(string $step, string $migrationUuid): void
    {
        $this->newLine();
        $this->line('Next step:');
        $this->line('  php artisan leo:migrate-local-vm --step=' . $step . ' --migration-id=' . $migrationUuid);
    }

    private function printSteps(): void
    {
        $this->table(['Step', 'Description'], [
            ['propose',          'Propose an evacuation plan (no DB changes)'],
            ['approve',          'Approve the plan and create a migration record'],
            ['pre-flight',       'Verify SSH to both hosts, local SR paths, and cross-host SSH reachability'],
            ['collect-metadata', 'Collect VM, disk, and NIC metadata from source'],
            ['coalesce-vhd',     'Check snapshots, trigger SR scan, wait for flat VHD'],
            ['shutdown',         'Gracefully shut down the source VM'],
            ['copy-vhd',         'Copy VHD files directly from source to target hypervisor via rsync over SSH'],
            ['rescan-sr',        'Scan target SR and confirm VDI detection'],
            ['recreate-vm',      'Recreate VM record, VBDs, and VIFs on target'],
            ['validate',         'Verify recreated VM has correct vCPUs, memory, disks, NICs'],
            ['sync-db',          'Update VirtualMachines, VirtualDiskImages, VirtualNetworkCards in DB'],
            ['start-vm',         'Start VM on target and wait for running state'],
            ['run',              'Run the full migration end-to-end'],
        ]);
    }

    private function unknownStep(string $step): void
    {
        $this->error('Unknown step: "' . $step . '"');
        $this->newLine();
        $this->printSteps();
    }
}
