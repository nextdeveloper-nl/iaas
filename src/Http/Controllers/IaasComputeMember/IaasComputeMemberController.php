<?php

namespace NextDeveloper\IAAS\Http\Controllers\IaasComputeMember;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IaasComputeMember\IaasComputeMemberUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberQueryFilter;
use NextDeveloper\IAAS\Services\IaasComputeMemberService;
use NextDeveloper\IAAS\Http\Requests\IaasComputeMember\IaasComputeMemberCreateRequest;

class IaasComputeMemberController extends AbstractController
{
    /**
    * This method returns the list of iaascomputemembers.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param IaasComputeMemberQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(IaasComputeMemberQueryFilter $filter, Request $request) {
        $data = IaasComputeMemberService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $iaasComputeMemberId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IaasComputeMemberService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created IaasComputeMember object on database.
    *
    * @param IaasComputeMemberCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(IaasComputeMemberCreateRequest $request) {
        $model = IaasComputeMemberService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasComputeMember object on database.
    *
    * @param $iaasComputeMemberId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($iaasComputeMemberId, IaasComputeMemberUpdateRequest $request) {
        $model = IaasComputeMemberService::update($iaasComputeMemberId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates IaasComputeMember object on database.
    *
    * @param $iaasComputeMemberId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($iaasComputeMemberId) {
        $model = IaasComputeMemberService::delete($iaasComputeMemberId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}