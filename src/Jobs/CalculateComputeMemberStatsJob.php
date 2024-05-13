<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CalculateComputeMemberStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Retrieve all compute members
        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each compute member
        foreach ($computeMembers as $computeMember)
        {
            // Insert compute member statistics into the database
            DB::table('iaas_compute_member_stats')->insert([
                'iaas_compute_member_id'    => $computeMember->id,
                'used_cpu'                  => $computeMember->used_cpu,
                'used_ram'                  => $computeMember->used_ram,
                'running_vm'                => $computeMember->running_vm,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
        }
    }
}
