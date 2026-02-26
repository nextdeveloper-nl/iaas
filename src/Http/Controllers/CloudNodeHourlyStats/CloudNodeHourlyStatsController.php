<?php

namespace NextDeveloper\IAAS\Http\Controllers\CloudNodeHourlyStats;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\CloudNodeHourlyStats\CloudNodeHourlyStatsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\CloudNodeHourlyStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\CloudNodeHourlyStats;
use NextDeveloper\IAAS\Services\CloudNodeHourlyStatsService;
use NextDeveloper\IAAS\Http\Requests\CloudNodeHourlyStats\CloudNodeHourlyStatsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class CloudNodeHourlyStatsController extends AbstractController
{
    private $model = CloudNodeHourlyStats::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of cloudnodehourlystats.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  CloudNodeHourlyStatsQueryFilter $filter  An object that builds search query
     * @param  Request                         $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CloudNodeHourlyStatsQueryFilter $filter, Request $request)
    {
        $data = CloudNodeHourlyStatsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = CloudNodeHourlyStatsService::getActions();

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
        $actionId = CloudNodeHourlyStatsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $cloudNodeHourlyStatsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = CloudNodeHourlyStatsService::getByRef($ref);

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
        $objects = CloudNodeHourlyStatsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created CloudNodeHourlyStats object on database.
     *
     * @param  CloudNodeHourlyStatsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(CloudNodeHourlyStatsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = CloudNodeHourlyStatsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates CloudNodeHourlyStats object on database.
     *
     * @param  $cloudNodeHourlyStatsId
     * @param  CloudNodeHourlyStatsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($cloudNodeHourlyStatsId, CloudNodeHourlyStatsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = CloudNodeHourlyStatsService::update($cloudNodeHourlyStatsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates CloudNodeHourlyStats object on database.
     *
     * @param  $cloudNodeHourlyStatsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($cloudNodeHourlyStatsId)
    {
        $model = CloudNodeHourlyStatsService::delete($cloudNodeHourlyStatsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
