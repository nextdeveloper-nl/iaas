<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\ComputePools\Scan;
use NextDeveloper\IAAS\Actions\Repositories\SynchronizeMachineImages;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncMarketplaceProducts extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:sync-repository-machine-images';

    /**
     * @var string
     */
    protected $description = 'Syncs the repository machine images in repositories';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        UserHelper::setUserById(config('leo.current_user_id'));
        UserHelper::setCurrentAccountById(config('leo.current_account_id'));

        $repos = Repositories::all();

        foreach ($repos as $repo) {
            dispatch(new SynchronizeMachineImages($repo));
        }
    }
}
