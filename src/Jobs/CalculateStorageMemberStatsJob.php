<?php

namespace NextDeveloper\IAAS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CalculateStorageMemberStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Retrieve all storage members
        $storageMembers = StorageMembers::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each storage member
        foreach ($storageMembers as $storageMember)
        {
            // Insert storage member statistics into the database
            DB::table('iaas_storage_member_stats')->insert([
                'iaas_storage_member_id'    => $storageMember->id,
                'used_disk'                 => $storageMember->used_disk,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
        }
    }
}
