<?php

namespace NextDeveloper\IAAS\Jobs\Sync;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Actions\ComputeMembers\Scan;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncComputeMembersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();
        // Retrieve all compute members
        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each compute member
        foreach ($computeMembers as $computeMember)
        {
            dispatch(new Scan($computeMember));
        }
    }
}
