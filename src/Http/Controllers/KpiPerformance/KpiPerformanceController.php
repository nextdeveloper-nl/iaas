<?php

namespace NextDeveloper\IAAS\Http\Controllers\KpiPerformance;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\KpiPerformance\KpiPerformanceUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\KpiPerformanceQueryFilter;
use NextDeveloper\IAAS\Database\Models\KpiPerformance;
use NextDeveloper\IAAS\Services\KpiPerformanceService;
use NextDeveloper\IAAS\Http\Requests\KpiPerformance\KpiPerformanceCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class KpiPerformanceController extends AbstractController
{
    private $model = KpiPerformance::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of kpiperformances.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  KpiPerformanceQueryFilter $filter  An object that builds search query
     * @param  Request                   $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(KpiPerformanceQueryFilter $filter, Request $request)
    {
        $data = KpiPerformanceService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = KpiPerformanceService::getActions();

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
        $actionId = KpiPerformanceService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $kpiPerformanceId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = KpiPerformanceService::getByRef($ref);

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
        $objects = KpiPerformanceService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created KpiPerformance object on database.
     *
     * @param  KpiPerformanceCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(KpiPerformanceCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = KpiPerformanceService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates KpiPerformance object on database.
     *
     * @param  $kpiPerformanceId
     * @param  KpiPerformanceUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($kpiPerformanceId, KpiPerformanceUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = KpiPerformanceService::update($kpiPerformanceId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates KpiPerformance object on database.
     *
     * @param  $kpiPerformanceId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($kpiPerformanceId)
    {
        $model = KpiPerformanceService::delete($kpiPerformanceId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
