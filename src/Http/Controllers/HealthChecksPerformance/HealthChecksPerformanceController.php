<?php

namespace NextDeveloper\IAAS\Http\Controllers\HealthChecksPerformance;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\HealthChecksPerformance\HealthChecksPerformanceUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\HealthChecksPerformanceQueryFilter;
use NextDeveloper\IAAS\Database\Models\HealthChecksPerformance;
use NextDeveloper\IAAS\Services\HealthChecksPerformanceService;
use NextDeveloper\IAAS\Http\Requests\HealthChecksPerformance\HealthChecksPerformanceCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class HealthChecksPerformanceController extends AbstractController
{
    private $model = HealthChecksPerformance::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of healthchecksperformances.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  HealthChecksPerformanceQueryFilter $filter  An object that builds search query
     * @param  Request                            $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(HealthChecksPerformanceQueryFilter $filter, Request $request)
    {
        $data = HealthChecksPerformanceService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = HealthChecksPerformanceService::getActions();

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * Makes the related action to the object
     *
     * @param  $objectId
     * @param  $action
     * @return array
     */
    public function doAction($objectId, $action)
    {
        $actionId = HealthChecksPerformanceService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $healthChecksPerformanceId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = HealthChecksPerformanceService::getByRef($ref);

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
        $objects = HealthChecksPerformanceService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created HealthChecksPerformance object on database.
     *
     * @param  HealthChecksPerformanceCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(HealthChecksPerformanceCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = HealthChecksPerformanceService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates HealthChecksPerformance object on database.
     *
     * @param  $healthChecksPerformanceId
     * @param  HealthChecksPerformanceUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($healthChecksPerformanceId, HealthChecksPerformanceUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = HealthChecksPerformanceService::update($healthChecksPerformanceId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates HealthChecksPerformance object on database.
     *
     * @param  $healthChecksPerformanceId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($healthChecksPerformanceId)
    {
        $model = HealthChecksPerformanceService::delete($healthChecksPerformanceId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
