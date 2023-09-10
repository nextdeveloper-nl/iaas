<?php

namespace NextDeveloper\IAAS\Http\Controllers\CloudNodes;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\CloudNodes\CloudNodesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\CloudNodesQueryFilter;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Http\Requests\CloudNodes\CloudNodesCreateRequest;

class CloudNodesController extends AbstractController
{
    /**
    * This method returns the list of cloudnodes.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param CloudNodesQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(CloudNodesQueryFilter $filter, Request $request) {
        $data = CloudNodesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $cloudNodesId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = CloudNodesService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created CloudNodes object on database.
    *
    * @param CloudNodesCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(CloudNodesCreateRequest $request) {
        $model = CloudNodesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates CloudNodes object on database.
    *
    * @param $cloudNodesId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($cloudNodesId, CloudNodesUpdateRequest $request) {
        $model = CloudNodesService::update($cloudNodesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates CloudNodes object on database.
    *
    * @param $cloudNodesId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($cloudNodesId) {
        $model = CloudNodesService::delete($cloudNodesId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}