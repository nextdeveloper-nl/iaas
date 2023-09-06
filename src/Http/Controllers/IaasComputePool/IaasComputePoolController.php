<?php

namespace NextDeveloper\IAAS\Http\Controllers\IaasComputePool;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IaasComputePool\IaasComputePoolUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IaasComputePoolQueryFilter;
use NextDeveloper\IAAS\Services\IaasComputePoolService;
use NextDeveloper\IAAS\Http\Requests\IaasComputePool\IaasComputePoolCreateRequest;

class IaasComputePoolController extends AbstractController
{
    /**
    * This method returns the list of iaascomputepools.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param IaasComputePoolQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(IaasComputePoolQueryFilter $filter, Request $request) {
        $data = IaasComputePoolService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $iaasComputePoolId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IaasComputePoolService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created IaasComputePool object on database.
    *
    * @param IaasComputePoolCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(IaasComputePoolCreateRequest $request) {
        $model = IaasComputePoolService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasComputePool object on database.
    *
    * @param $iaasComputePoolId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($iaasComputePoolId, IaasComputePoolUpdateRequest $request) {
        $model = IaasComputePoolService::update($iaasComputePoolId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasComputePool object on database.
    *
    * @param $iaasComputePoolId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($iaasComputePoolId) {
        $model = IaasComputePoolService::delete($iaasComputePoolId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}