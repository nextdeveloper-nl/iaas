<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * Detects VMs that were manually migrated between hosts with local disk (xe vm-export/vm-import
 * done by hand, outside of leo:migrate-local-vm), recognized by a name-label that was set to the
 * original VM's uuid to dodge a name-label collision, and repoints the original VM record onto
 * the newly imported hypervisor object. See VirtualMachinesService::mergeMigratedLocalDiskVm().
 */
class FixMigratedLocalVirtualMachine extends Command
{
    /**
     * @var string
     */
    protected $signature = 'leo:fix-migrated-local-vm
        {--vm= : Only consider the target VM with this uuid (the server that was migrated)}
        {--fix : Apply the fix. Without this flag the command only reports what it would do.}';

    /**
     * @var string
     */
    protected $description = 'Finds VMs manually migrated with local disk (name-label set to the old VM uuid) and repoints the original VM record onto the new hypervisor object.';

    public function handle()
    {
        $vmUuid = $this->option('vm');
        $fix = (bool) $this->option('fix');

        $candidates = VirtualMachinesService::findMigratedLocalDiskVmCandidates();

        if ($vmUuid) {
            $candidates = $candidates->filter(fn ($pair) => $pair['target']->uuid === $vmUuid);
        }

        if ($candidates->isEmpty()) {
            $this->info('No migrated local-disk VMs found.');

            return;
        }

        $this->table(
            ['Target VM (original)', 'Target uuid', 'Orphan record (new import)', 'New host id', 'New hypervisor uuid'],
            $candidates->map(fn ($pair) => [
                $pair['target']->name,
                $pair['target']->uuid,
                $pair['orphan']->uuid,
                $pair['orphan']->iaas_compute_member_id,
                $pair['orphan']->hypervisor_uuid,
            ])->toArray()
        );

        if (!$fix) {
            $this->warn('Dry run - pass --fix to apply. Nothing has been changed.');

            return;
        }

        foreach ($candidates as $pair) {
            try {
                VirtualMachinesService::mergeMigratedLocalDiskVm($pair['orphan'], $pair['target']);

                $this->info('Repointed ' . $pair['target']->uuid . ' (' . $pair['target']->name . ') onto its new ' .
                    'hypervisor object - was tracked as orphan ' . $pair['orphan']->uuid . '.');
            } catch (\Exception $e) {
                $this->error('Failed to fix ' . $pair['target']->uuid . ': ' . $e->getMessage());
                Log::error('[FixMigratedLocalVirtualMachine] Failed to fix ' . $pair['target']->uuid . ': ' . $e->getMessage());
            }
        }

        $this->line('');
        $this->comment('The old VM object on the source host has NOT been touched - remove it by hand once you have confirmed the migration is good.');
    }
}
