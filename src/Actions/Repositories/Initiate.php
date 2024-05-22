<?php

namespace NextDeveloper\IAAS\Actions\Repositories;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Services\Repositories\RepositoryUpdateService;

/**
 * This action will scan compute member and sync all findings
 */
class Initiate extends AbstractAction
{
    public const EVENTS = [
        'initiating:NextDeveloper\IAAS\Repositories',
        'initiated:NextDeveloper\IAAS\Repositories'
    ];

    public const PARAMS = [];

    public function __construct(Repositories $repositories)
    {
        parent::__construct();

        $this->model = $repositories;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiating repository: ' . $this->model->name);

        Events::fire('initiating:NextDeveloper\IAAS\Repositories', $this->model);

        $this->setProgress(10, 'Updating repository information');

        RepositoryUpdateService::updateInformation($this->model);

        $this->setProgress(10, 'Syncing virtual machine images. This can take some time ...');

        //(new SynchronizeIsos($this->model))->handle();
        //new SynchronizeMachineImages($this->model))->handle();

        //  This feature will come later
        //  (new SynchronizeDockerImages($this->model))->handle();

        $this->setProgress(100, 'Repository is initiated/updated.');
    }
}
