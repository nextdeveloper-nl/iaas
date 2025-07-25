<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action will mount the given repository to compute member to import virtual machines.
 */
class UnmountVmRepo extends AbstractAction
{
    private $repo;

    public const EVENTS = [
        'unmounting:NextDeveloper\IAAS\Repositories',
        'unmounted:NextDeveloper\IAAS\Repositories',
        'repo-unmounted:NextDeveloper\IaaS\ComputeMembers'
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

        parent::__construct($params);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate repository unmounting service.');

        Events::fire('unmounting:NextDeveloper\IAAS\Repositories', $this->model);

        $result = ComputeMemberXenService::unmountVmRepository($this->model, $this->repo);

        if(!$result)
            $this->setFinishedWithError('Repository unmounting failed');

        Events::fire('unmounted:NextDeveloper\IAAS\Repositories', $this->model);

        $this->setProgress(100, 'Repository unmounted');
    }
}
