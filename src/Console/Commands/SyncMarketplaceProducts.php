<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\ComputePools\Scan;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncMarketplaceProducts extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:sync-marketplace-products';

    /**
     * @var string
     */
    protected $description = 'Syncs the cloud node with the given cloud node name. This command ' .
    'takes slug as parameter';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        UserHelper::setUserById(config('leo.current_user_id'));
        UserHelper::setCurrentAccountById(config('leo.current_account_id'));


    }
}
