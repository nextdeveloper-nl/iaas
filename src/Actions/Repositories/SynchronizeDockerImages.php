<?php

namespace NextDeveloper\IAAS\Actions\Repositories;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Services\Repositories\SyncDockerRegistryService;

/**
 * This action will scan compute member and sync all findings
 */
class SynchronizeDockerImages extends AbstractAction
{
    public const EVENTS = [
        'syncing-docker-images:NextDeveloper\IAAS\Repositories',
        'docker-images-synced:NextDeveloper\IAAS\Repositories'
    ];

    public function __construct(Repositories $repo, $params = null, $previousAction = null)
    {
        trigger_error('This class is deprecated because there is no reason to host image information in database or sync.');

        $this->model = $repo;

        $this->queue = 'iaas';

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate storage member started');

        Events::fire('checked:NextDeveloper\IAAS\StorageMembers', $this->model);

        SyncDockerRegistryService::syncRepoImages($this->model);

        $this->setProgress(100, 'Storage member scanned and synced');
    }
}
