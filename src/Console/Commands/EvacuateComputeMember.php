<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\EvacuationService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Generates the commands needed to evacuate every VM from one compute member to another.
 *
 * Read-only: nothing is migrated and no migration record is created. For each VM on the
 * source host it runs a dry-run plan (EvacuationService::proposePlan) and prints the
 * artisan commands to run:
 *   - VM has any local disk (EXT/LVM)  -> leo:migrate-local-vm, disks land on local SRs of the target
 *   - all disks on shared storage (NFS) -> leo:migrate-vm, disks land on shared storage of the target
 *
 * Usage:
 *
 *   php artisan leo:evacuate --source-id=<compute-member-uuid> --target-id=<compute-member-uuid>
 *
 *   # Local-disk VMs need a shared NFS directory for the export/import
 *   php artisan leo:evacuate --source-id=<uuid> --target-id=<uuid> --nfs-share=<server>:/<path>
 *
 *   # Only print the commands (pipe into a file / shell script)
 *   php artisan leo:evacuate --source-id=<uuid> --target-id=<uuid> --commands-only > evacuate.sh
 */
class EvacuateComputeMember extends Command
{
    protected $signature = 'leo:evacuate
        {--source-id= : UUID of the ComputeMember to evacuate}
        {--target-id= : UUID of the ComputeMember that receives the VMs}
        {--nfs-share= : NFS share (server:/path) both hosts mount for local-disk VM exports (default: IAAS_MIGRATION_NFS_SHARE)}
        {--commands-only : Print only the artisan commands (infeasible VMs are emitted as comments)}';

    protected $description = 'Generate the commands to migrate all VMs from one compute member to another (shared disks stay shared, local disks stay local)';

    public function handle(): int
    {
        UserHelper::setAdminAsCurrentUser();

        try {
            [$source, $target] = $this->resolveComputeMembers();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if ($source->id === $target->id) {
            $this->error('Source and target compute member are the same.');
            return self::FAILURE;
        }

        $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_member_id', $source->id)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        // Share used by the generated leo:migrate-local-vm commands (flag, else IAAS_MIGRATION_NFS_SHARE).
        $nfsShare = $this->option('nfs-share') ?: config('iaas.migration.nfs_share');

        $this->note("Evacuating {$source->name} -> {$target->name}: {$vms->count()} VM(s) found.");

        $feasibleCount = 0;

        foreach ($vms as $vm) {
            try {
                $storageKind = $this->detectStorageKind($vm);
                $options     = $this->buildStorageOptions($vm);
                $plan        = EvacuationService::proposePlan($vm, $target, $options);
            } catch (\Throwable $e) {
                $this->emitComment("SKIP {$vm->name} ({$vm->uuid}): " . $e->getMessage());
                continue;
            }

            if (!$plan['is_feasible']) {
                $this->emitComment("NOT FEASIBLE {$vm->name} ({$vm->uuid}): " . implode(' | ', $plan['warnings']));
                continue;
            }

            $feasibleCount++;

            $command  = $storageKind === 'local' ? 'leo:migrate-local-vm' : 'leo:migrate-vm';
            $nfsFlag  = '';

            if ($storageKind === 'local') {
                if (empty($nfsShare)) {
                    $this->emitComment("SKIP {$vm->name} ({$vm->uuid}): local disks need --nfs-share or IAAS_MIGRATION_NFS_SHARE");
                    $feasibleCount--;
                    continue;
                }

                // Passed explicitly so the generated commands do not depend on the env of the shell running them.
                $nfsFlag = ' --nfs-share=' . escapeshellarg($nfsShare);
            }

            $this->emitComment("{$vm->name} ({$vm->uuid}) status={$vm->status} disks={$storageKind}");
            // approve prompts for confirmation; --no-interaction accepts the default (yes).
            $this->line("php artisan {$command} --step=approve --vm-id={$vm->uuid} --target-id={$target->uuid}{$nfsFlag} --no-interaction");
            // The migration UUID only exists after approve, so it is printed by approve and used here.
            $this->line("php artisan {$command} --step=run --migration-id=<migration-uuid-from-approve>{$nfsFlag}");
            $this->line('');
        }

        $this->note("{$feasibleCount}/{$vms->count()} VM(s) can be evacuated.");

        return self::SUCCESS;
    }

    /**
     * Returns 'local' if any non-CDROM disk of the VM is on a non-NFS volume, otherwise 'shared'.
     */
    private function detectStorageKind(VirtualMachines $vm): string
    {
        foreach ($this->getDisks($vm) as $disk) {
            $sv = $this->getVolume($disk);

            if ($sv && $sv->disk_physical_type !== 'nfs') {
                return 'local';
            }
        }

        return 'shared';
    }

    /**
     * Pins every disk to its own storage type so the plan picks a local SR for local
     * disks and a shared (NFS) SR for shared disks on the target.
     */
    private function buildStorageOptions(VirtualMachines $vm): array
    {
        $perDiskTypes = [];

        foreach ($this->getDisks($vm) as $disk) {
            $sv = $this->getVolume($disk);

            if ($sv && $sv->disk_physical_type) {
                $perDiskTypes[$disk->uuid] = $sv->disk_physical_type;
            }
        }

        return empty($perDiskTypes) ? [] : ['disk_storage_type' => $perDiskTypes];
    }

    private function getDisks(VirtualMachines $vm)
    {
        return VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('is_cdrom', false)
            ->whereNull('deleted_at')
            ->get();
    }

    private function getVolume(VirtualDiskImages $disk): ?StorageVolumes
    {
        if (!$disk->iaas_storage_volume_id) {
            return null;
        }

        return StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $disk->iaas_storage_volume_id)
            ->first();
    }

    private function resolveComputeMembers(): array
    {
        $sourceId = $this->option('source-id');
        $targetId = $this->option('target-id');

        if (empty($sourceId) || empty($targetId)) {
            throw new \InvalidArgumentException('Both --source-id and --target-id are required.');
        }

        $find = fn (string $uuid) => ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        $source = $find($sourceId);
        $target = $find($targetId);

        if (!$source) {
            throw new \InvalidArgumentException('Source ComputeMember not found with UUID: ' . $sourceId);
        }

        if (!$target) {
            throw new \InvalidArgumentException('Target ComputeMember not found with UUID: ' . $targetId);
        }

        return [$source, $target];
    }

    /** Informational line; hidden in --commands-only mode so stdout stays a clean script. */
    private function note(string $message): void
    {
        if (!$this->option('commands-only')) {
            $this->info($message);
        }
    }

    /** Shell comment, so --commands-only output is still a valid script. */
    private function emitComment(string $message): void
    {
        $this->line('# ' . $message);
    }
}
