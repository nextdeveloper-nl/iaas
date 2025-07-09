<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Actions\Repositories\SynchronizeMachineImages;
use NextDeveloper\IAAS\Database\Models\Repositories;
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
