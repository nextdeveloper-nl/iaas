<?php

namespace NextDeveloper\IAAS\Http\Controllers\ComputePools;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\ComputePools\ComputePoolsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\ComputePoolsQueryFilter;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAAS\Http\Requests\ComputePools\ComputePoolsCreateRequest;

class ComputePoolsController extends AbstractController
{
    /**
     * This method returns the list of computepools.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  ComputePoolsQueryFilter $filter  An object that builds search query
     * @param  Request                 $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ComputePoolsQueryFilter $filter, Request $request)
    {
        $data = ComputePoolsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $computePoolsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = ComputePoolsService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method created ComputePools object on database.
     *
     * @param  ComputePoolsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(ComputePoolsCreateRequest $request)
    {
        $model = ComputePoolsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputePools object on database.
     *
     * @param  $computePoolsId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($computePoolsId, ComputePoolsUpdateRequest $request)
    {
        $model = ComputePoolsService::update($computePoolsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputePools object on database.
     *
     * @param  $computePoolsId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($computePoolsId)
    {
        $model = ComputePoolsService::delete($computePoolsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
