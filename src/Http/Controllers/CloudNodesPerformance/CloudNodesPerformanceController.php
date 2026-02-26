<?php

namespace NextDeveloper\IAAS\Http\Controllers\CloudNodesPerformance;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\CloudNodesPerformance\CloudNodesPerformanceUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\CloudNodesPerformanceQueryFilter;
use NextDeveloper\IAAS\Database\Models\CloudNodesPerformance;
use NextDeveloper\IAAS\Services\CloudNodesPerformanceService;
use NextDeveloper\IAAS\Http\Requests\CloudNodesPerformance\CloudNodesPerformanceCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class CloudNodesPerformanceController extends AbstractController
{
    private $model = CloudNodesPerformance::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of cloudnodesperformances.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  CloudNodesPerformanceQueryFilter $filter  An object that builds search query
     * @param  Request                          $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CloudNodesPerformanceQueryFilter $filter, Request $request)
    {
        $data = CloudNodesPerformanceService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = CloudNodesPerformanceService::getActions();

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
        $actionId = CloudNodesPerformanceService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $cloudNodesPerformanceId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = CloudNodesPerformanceService::getByRef($ref);

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
        $objects = CloudNodesPerformanceService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created CloudNodesPerformance object on database.
     *
     * @param  CloudNodesPerformanceCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(CloudNodesPerformanceCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = CloudNodesPerformanceService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates CloudNodesPerformance object on database.
     *
     * @param  $cloudNodesPerformanceId
     * @param  CloudNodesPerformanceUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($cloudNodesPerformanceId, CloudNodesPerformanceUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = CloudNodesPerformanceService::update($cloudNodesPerformanceId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates CloudNodesPerformance object on database.
     *
     * @param  $cloudNodesPerformanceId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($cloudNodesPerformanceId)
    {
        $model = CloudNodesPerformanceService::delete($cloudNodesPerformanceId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
