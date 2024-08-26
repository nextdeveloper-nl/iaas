<?php

namespace NextDeveloper\IAAS\Actions\Repositories;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;

/**
 * This action will scan compute member and sync all findings
 */
class SynchronizeDockerImages extends AbstractAction
{
    public const EVENTS = [
        'syncing-docker-images:NextDeveloper\IAAS\Repositories',
        'docker-images-synced:NextDeveloper\IAAS\Repositories'
    ];

    public function __construct(Repositories $repo)
    {
        parent::__construct();

        $this->queue = 'iaas';

        $this->model = $repo;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate storage member started');

        Events::fire('checked:NextDeveloper\IAAS\StorageMembers', $this->model);

        $this->setProgress(100, 'Storage member scanned and synced');
    }
}
