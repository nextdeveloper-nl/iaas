<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Actions\ComputePools\Scan;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncCloudNode extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:sync-cloud-node {slug?}';

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

        $node = $this->argument('slug');

        if(!$node) {
            $nodes = CloudNodesService::getSlugsOfNodes();
            $choices = [];

            foreach ($nodes as $node) {
                $choices[] = $node->slug;
            }

            $choices[] = 'all';

            $slug = $this->choice(
                'Please provide a cloud node slug, for me to sync. If you want me to sync all, please write all.',
                $choices,
                (count($choices) - 1),
                3,
                false
            );

            if(!$slug) {
                $this->error('Cannot understand which cloud node you are asking for, thats why I am quiting.');
                return;
            }
        }

        $this->line('Starting to sync the given cloud node.');

        if($slug == 'all') {
            $cloudNodes = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
                ->get();
        } else {
            $cloudNodes = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
                ->where('slug', $slug)
                ->get();
        }

        foreach ($cloudNodes as $node) {
            $computePools = CloudNodesService::getComputePools($node);

            foreach ($computePools as $pool) {
                dispatch(new Scan($pool));
            }
        }
    }
}
