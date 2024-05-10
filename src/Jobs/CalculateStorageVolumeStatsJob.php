<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CalculateStorageVolumeStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Retrieve all storage volumes
        $storageVolumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each storage volume
        foreach ($storageVolumes as $storageVolume)
        {
            // Insert storage volume statistics into the database
            DB::table('iaas_storage_volume_stats')->insert([
                'iaas_storage_volume_id'    => $storageVolume->id,
                'used_disk'                 => $storageVolume->used_hdd,
                'free_disk'                 => $storageVolume->free_hdd,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
        }
    }
}
