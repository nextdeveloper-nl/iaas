<?php

namespace NextDeveloper\IAAS\Http\Controllers\IaasCloudNode;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IaasCloudNode\IaasCloudNodeUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IaasCloudNodeQueryFilter;
use NextDeveloper\IAAS\Services\IaasCloudNodeService;
use NextDeveloper\IAAS\Http\Requests\IaasCloudNode\IaasCloudNodeCreateRequest;

class IaasCloudNodeController extends AbstractController
{
    /**
    * This method returns the list of iaascloudnodes.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param IaasCloudNodeQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(IaasCloudNodeQueryFilter $filter, Request $request) {
        $data = IaasCloudNodeService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $iaasCloudNodeId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IaasCloudNodeService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created IaasCloudNode object on database.
    *
    * @param IaasCloudNodeCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(IaasCloudNodeCreateRequest $request) {
        $model = IaasCloudNodeService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasCloudNode object on database.
    *
    * @param $iaasCloudNodeId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($iaasCloudNodeId, IaasCloudNodeUpdateRequest $request) {
        $model = IaasCloudNodeService::update($iaasCloudNodeId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasCloudNode object on database.
    *
    * @param $iaasCloudNodeId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($iaasCloudNodeId) {
        $model = IaasCloudNodeService::delete($iaasCloudNodeId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}