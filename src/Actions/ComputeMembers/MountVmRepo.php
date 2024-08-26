<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Repositories\RepositoryMountService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action will mount the given repository to compute member to import virtual machines.
 */
class MountVmRepo extends AbstractAction
{
    private $repo;

    public const EVENTS = [
        'mounting:NextDeveloper\IAAS\Repositories',
        'mounted:NextDeveloper\IAAS\Repositories',
        'repo-mounted:NextDeveloper\IaaS\ComputeMembers'
    ];

    public const PARAMS = [
        'iaas_repository_id' =>  'required|exists:iaas_repositories,uuid',
    ];

    public function __construct(ComputeMembers $computeMember, array $params)
    {
        $this->queue = 'iaas';

        $this->model = $computeMember;
        $this->repo = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $params['iaas_repository_id'])
            ->first();

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate repository mounting service.');

        Events::fire('mounting:NextDeveloper\IAAS\Repositories', $this->model);

        $result = ComputeMemberXenService::mountVmRepository($this->model, $this->repo);

        if(!$result)
            $this->setFinishedWithError('Repository mounting failed');

        Events::fire('mounted:NextDeveloper\IAAS\Repositories', $this->model);

        $this->setProgress(100, 'Repository mounted');
    }
}
