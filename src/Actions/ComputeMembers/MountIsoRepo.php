<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Repositories\RepositoryMountService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action will mount the given repository to compute member to import virtual machines.
 */
class MountIsoRepo extends AbstractAction
{
    private $repo;

    public const EVENTS = [
        'mounting:NextDeveloper\IAAS\Repositories',
        'mounted:NextDeveloper\IAAS\Repositories',
        'mount-failed:NextDeveloper\IAAS\Repositories',
        'repo-mounted:NextDeveloper\IaaS\ComputeMembers'
    ];

    public const PARAMS = [
        'iaas_repository_id' =>  'required|exists:iaas_repositories,uuid',
    ];

    public function __construct(ComputeMembers $computeMember, array $params)
    {
        if(array_key_exists(0, $params))
            $params = $params[0];

        $this->model = $computeMember;
        $this->repo = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $params['iaas_repository_id'])
            ->first();

        parent::__construct($params);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate repository mounting service.');

        if(!$this->isoStateCheck())
            return false;

        Events::fire('mounting:NextDeveloper\IAAS\Repositories', $this->model);

        $result = ComputeMemberXenService::mountIsoRepository($this->model, $this->repo);

        if(!$result) {
            Events::fire('mount-failed:NextDeveloper\IAAS\Repositories', $this->model);
            $this->setFinishedWithError('Repository mounting failed');
        }

        Events::fire('mounted:NextDeveloper\IAAS\Repositories', $this->model);

        $this->setProgress(100, 'Repository mounted');
    }

    private function isoStateCheck()
    {
        if(!$this->repo->iso_path) {
            StateHelper::setState($this->repo, 'iso_repo', 'not_configured');

            $this->repo->update([
                'is_iso_repo'   =>  false
            ]);

            $this->setFinishedWithError('ISO repository not configured. You need to check' .
                ' the machine image directory, if its available or you provided the correct path.');

            Events::fire('cannot-sync-isos:NextDeveloper\IAAS\Repositories', $this->repo);

            return false;
        }

        $this->repo->update([
            'is_iso_repo'   =>  true
        ]);

        StateHelper::setState($this->repo, 'iso_repo', 'Iso repository is configured');

        return true;
    }
}
