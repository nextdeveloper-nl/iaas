<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Elasticsearch\VirtualMachinesIndexMapping;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Backfills/reindexes all VirtualMachines rows into Elasticsearch. Run manually
 * pre-cutover and after any mapping change - not scheduled.
 *
 * Uses index aliasing for zero-downtime reindexing: creates a new versioned physical
 * index (leo_iaas_virtual_machines_v{N}), bulk-indexes into it, then atomically
 * repoints the leo_iaas_virtual_machines alias the app always reads/writes through.
 */
class ReindexVirtualMachines extends Command
{
    protected $signature = 'leo:reindex-virtual-machines {--index-version=} {--chunk=500}';

    protected $description = 'Backfills/reindexes all VirtualMachines rows into Elasticsearch behind a versioned alias.';

    public function handle(Client $client): int
    {
        $prefix = config('elasticsearch.index_prefix', 'leo');
        $alias = $prefix . '_iaas_virtual_machines';
        $version = $this->option('index-version') ?: time();
        $physicalIndex = $alias . '_v' . $version;
        $chunkSize = (int) $this->option('chunk');

        $this->line("Creating index {$physicalIndex}...");

        $client->indices()->create([
            'index' => $physicalIndex,
            'body' => [
                'settings' => VirtualMachinesIndexMapping::settings(),
                'mappings' => VirtualMachinesIndexMapping::mappings(),
            ],
        ]);

        $total = 0;

        //  Bypasses tenant/limit scoping (this is an admin backfill, not tied to any
        //  one tenant) but deliberately NOT withoutGlobalScopes() - that would also
        //  strip Laravel's automatic SoftDeletingScope and index soft-deleted rows,
        //  which the normal list endpoint (and every other query in this codebase)
        //  excludes by default. Found the hard way: an earlier withoutGlobalScopes()
        //  version indexed 1701 trashed VMs alongside the 243 real ones.
        VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($vms) use ($client, $physicalIndex, &$total) {
                $body = [];

                foreach ($vms as $vm) {
                    $body[] = ['index' => ['_index' => $physicalIndex, '_id' => $vm->uuid]];
                    $body[] = $vm->toElasticDocument();
                }

                if ($body) {
                    $response = $client->bulk(['body' => $body]);

                    if ($response['errors']) {
                        foreach ($response['items'] as $item) {
                            if (isset($item['index']['error'])) {
                                $this->error('Failed to index ' . $item['index']['_id'] . ': ' . json_encode($item['index']['error']));
                            }
                        }
                    }
                }

                $total += count($vms);
                $this->line("Indexed {$total} virtual machines so far...");
            });

        $this->line("Repointing alias {$alias} -> {$physicalIndex}...");

        $existingIndices = [];

        try {
            $existingIndices = array_keys($client->indices()->getAlias(['name' => $alias])->asArray());
        } catch (\Throwable $e) {
            // Alias doesn't exist yet - first-ever reindex, nothing to remove.
        }

        $actions = [
            ['add' => ['index' => $physicalIndex, 'alias' => $alias]],
        ];

        foreach ($existingIndices as $oldIndex) {
            if ($oldIndex !== $physicalIndex) {
                $actions[] = ['remove' => ['index' => $oldIndex, 'alias' => $alias]];
            }
        }

        $client->indices()->updateAliases(['body' => ['actions' => $actions]]);

        $this->info("Done. {$total} virtual machines indexed into {$physicalIndex}, alias {$alias} repointed.");

        return self::SUCCESS;
    }
}
