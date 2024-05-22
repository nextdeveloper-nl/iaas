<?php

namespace NextDeveloper\IAAS\Actions\Repositories;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;

/**
 * This action will scan compute member and sync all findings
 */
class SynchronizeIsos extends AbstractAction
{
    public const EVENTS = [
        'syncing-isos:NextDeveloper\IAAS\Repositories',
        'isos-synced:NextDeveloper\IAAS\Repositories',
        'cannot-sync-isos:NextDeveloper\IAAS\Repositories'
    ];

    public function __construct(Repositories $repo)
    {
        parent::__construct();

        $this->model = $repo;
    }

    public function handle()
    {
        $this->setProgress(0, 'Syncronizing ISO images in repository.');

        Events::fire('syncing-isos:NextDeveloper\IAAS\Repositories', $this->model);

        if(!$this->model->iso_path) {
            StateHelper::setState($this->model, 'iso_repo', 'not_configured');

            $this->model->update([
                'is_iso_repo'   =>  false
            ]);

            $this->setFinishedWithError('ISO repository not configured. You need to check' .
                ' the machine image directory, if its available or you provided the correct path.');

            Events::fire('cannot-sync-isos:NextDeveloper\IAAS\Repositories', $this->model);

            return;
        }

        return;

        $this->setProgress(100, 'Storage member scanned and synced');
    }
}
