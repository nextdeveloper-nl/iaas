<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Actions\StorageVolumes\Scan;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncStorageVolume extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:sync-storage-volume {--uuid=} {--fg=}';

    /**
     * @var string
     */
    protected $description = 'Sync storage volumes.';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        UserHelper::setAdminAsCurrentUser();

        $storageVolumes = null;

        if($this->option('uuid')) {
            $storageVolumes = StorageVolumes::where('uuid', $this->option('uuid'))->get();
        } else {
            $storageVolumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)->get();
        }

        foreach ($storageVolumes as $storageVolume) {
            $job = new Scan($storageVolume);

            if($this->option('fg')) {
                $this->info('Syncing storage volume: ' . $storageVolume->name . ' (' . $storageVolume->uuid . ')');

                $job->handle();
            } else {
                $this->info('Dispatching job to sync storage volume: ' . $storageVolume->name . ' (' . $storageVolume->uuid . ')');

                dispatch($job);
            }
        }
    }
}
