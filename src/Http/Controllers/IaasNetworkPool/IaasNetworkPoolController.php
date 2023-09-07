<?php

namespace NextDeveloper\IAAS\Http\Controllers\IaasNetworkPool;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IaasNetworkPool\IaasNetworkPoolUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkPoolQueryFilter;
use NextDeveloper\IAAS\Services\IaasNetworkPoolService;
use NextDeveloper\IAAS\Http\Requests\IaasNetworkPool\IaasNetworkPoolCreateRequest;

class IaasNetworkPoolController extends AbstractController
{
    /**
    * This method returns the list of iaasnetworkpools.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param IaasNetworkPoolQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(IaasNetworkPoolQueryFilter $filter, Request $request) {
        $data = IaasNetworkPoolService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $iaasNetworkPoolId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IaasNetworkPoolService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created IaasNetworkPool object on database.
    *
    * @param IaasNetworkPoolCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(IaasNetworkPoolCreateRequest $request) {
        $model = IaasNetworkPoolService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasNetworkPool object on database.
    *
    * @param $iaasNetworkPoolId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($iaasNetworkPoolId, IaasNetworkPoolUpdateRequest $request) {
        $model = IaasNetworkPoolService::update($iaasNetworkPoolId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasNetworkPool object on database.
    *
    * @param $iaasNetworkPoolId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($iaasNetworkPoolId) {
        $model = IaasNetworkPoolService::delete($iaasNetworkPoolId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}