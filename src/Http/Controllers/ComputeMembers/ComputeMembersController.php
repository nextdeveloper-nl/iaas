<?php

namespace NextDeveloper\IAAS\Http\Controllers\ComputeMembers;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\ComputeMembers\ComputeMembersUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\ComputeMembersQueryFilter;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Http\Requests\ComputeMembers\ComputeMembersCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;
class ComputeMembersController extends AbstractController
{
    private $model = ComputeMembers::class;

    use Tags;
    /**
     * This method returns the list of computemembers.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  ComputeMembersQueryFilter $filter  An object that builds search query
     * @param  Request                   $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ComputeMembersQueryFilter $filter, Request $request)
    {
        $data = ComputeMembersService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $computeMembersId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = ComputeMembersService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method returns the list of sub objects the related object. Sub object means an object which is preowned by
     * this object.
     *
     * It can be tags, addresses, states etc.
     *
     * @param  $ref
     * @param  $subObject
     * @return void
     */
    public function relatedObjects($ref, $subObject)
    {
        $objects = ComputeMembersService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created ComputeMembers object on database.
     *
     * @param  ComputeMembersCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(ComputeMembersCreateRequest $request)
    {
        $model = ComputeMembersService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputeMembers object on database.
     *
     * @param  $computeMembersId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($computeMembersId, ComputeMembersUpdateRequest $request)
    {
        $model = ComputeMembersService::update($computeMembersId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputeMembers object on database.
     *
     * @param  $computeMembersId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($computeMembersId)
    {
        $model = ComputeMembersService::delete($computeMembersId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
