<?php

namespace NextDeveloper\IAAS\Http\Controllers\IaasStoragePool;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IaasStoragePool\IaasStoragePoolUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IaasStoragePoolQueryFilter;
use NextDeveloper\IAAS\Services\IaasStoragePoolService;
use NextDeveloper\IAAS\Http\Requests\IaasStoragePool\IaasStoragePoolCreateRequest;

class IaasStoragePoolController extends AbstractController
{
    /**
    * This method returns the list of iaasstoragepools.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param IaasStoragePoolQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(IaasStoragePoolQueryFilter $filter, Request $request) {
        $data = IaasStoragePoolService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $iaasStoragePoolId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IaasStoragePoolService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created IaasStoragePool object on database.
    *
    * @param IaasStoragePoolCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(IaasStoragePoolCreateRequest $request) {
        $model = IaasStoragePoolService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasStoragePool object on database.
    *
    * @param $iaasStoragePoolId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($iaasStoragePoolId, IaasStoragePoolUpdateRequest $request) {
        $model = IaasStoragePoolService::update($iaasStoragePoolId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasStoragePool object on database.
    *
    * @param $iaasStoragePoolId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($iaasStoragePoolId) {
        $model = IaasStoragePoolService::delete($iaasStoragePoolId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}