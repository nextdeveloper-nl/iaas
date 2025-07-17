<?php

namespace NextDeveloper\IAAS\Http\Controllers\ComputeMembers;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\ComputeMembers\ComputeMembersUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\ComputeMembersQueryFilter;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Http\Requests\ComputeMembers\ComputeMembersCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\EventsXenService;

class ComputeMemberEventsController extends AbstractController
{
    private $model = ComputeMembers::class;

    /**
     * This method created ComputeMembers object on database.
     *
     * @param  ComputeMembersCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(ComputeMembersCreateRequest $request)
    {
        return ResponsableFactory::makeResponse($this, EventsXenService::store($request->get('event')));
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
