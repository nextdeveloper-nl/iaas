<?php

namespace NextDeveloper\IAAS\Actions\Repositories;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Services\Repositories\SyncRepositoryService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action will scan compute member and sync all findings
 */
class SynchronizeMachineImages extends AbstractAction
{
    public const EVENTS = [
        'syncing-machine-images:NextDeveloper\IAAS\Repositories',
        'machine-images-synced:NextDeveloper\IAAS\Repositories',
        'cannot-sync-machine-images:NextDeveloper\IAAS\Repositories'
    ];

    public function __construct(Repositories $repo, $params = null, $previous = null)
    {
        UserHelper::setAdminAsCurrentUser();

        $this->model = $repo;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Starting sync machine images for' .
            ' repository: ' . $this->model->name);

        Events::fire('syncing-machine-images:NextDeveloper\IAAS\StorageMembers', $this->model);

        if(!$this->model->vm_path) {
            StateHelper::setState($this->model, 'vm_repo', 'not_configured');

            $this->model->update([
                'is_vm_repo'   =>  false
            ]);

            $this->setFinishedWithError('Machine image repository not configured. You need to check' .
                ' the machine image directory, if its available or you provided the correct path.');

            Events::fire('syncing-machine-images:NextDeveloper\IAAS\StorageMembers', $this->model);
            return;
        }

        SyncRepositoryService::syncRepoImages($this->model);

        Events::fire('cannot-sync-machine-images:NextDeveloper\IAAS\StorageMembers', $this->model);

        $this->setProgress(100, 'Machine image syncronization finished');
    }
}
