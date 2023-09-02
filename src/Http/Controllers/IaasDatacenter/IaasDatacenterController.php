<?php

namespace NextDeveloper\IAAS\Http\Controllers\IaasDatacenter;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IaasDatacenter\IaasDatacenterUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IaasDatacenterQueryFilter;
use NextDeveloper\IAAS\Services\IaasDatacenterService;
use NextDeveloper\IAAS\Http\Requests\IaasDatacenter\IaasDatacenterCreateRequest;

class IaasDatacenterController extends AbstractController
{
    /**
    * This method returns the list of iaasdatacenters.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param IaasDatacenterQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(IaasDatacenterQueryFilter $filter, Request $request) {
        $data = IaasDatacenterService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $iaasDatacenterId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IaasDatacenterService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created IaasDatacenter object on database.
    *
    * @param IaasDatacenterCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(IaasDatacenterCreateRequest $request) {
        $model = IaasDatacenterService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasDatacenter object on database.
    *
    * @param $iaasDatacenterId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($iaasDatacenterId, IaasDatacenterUpdateRequest $request) {
        $model = IaasDatacenterService::update($iaasDatacenterId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasDatacenter object on database.
    *
    * @param $iaasDatacenterId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($iaasDatacenterId) {
        $model = IaasDatacenterService::delete($iaasDatacenterId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}