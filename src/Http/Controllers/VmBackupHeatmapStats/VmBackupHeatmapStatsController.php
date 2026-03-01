<?php

namespace NextDeveloper\IAAS\Http\Controllers\VmBackupHeatmapStats;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VmBackupHeatmapStats\VmBackupHeatmapStatsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VmBackupHeatmapStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmapStats;
use NextDeveloper\IAAS\Services\VmBackupHeatmapStatsService;
use NextDeveloper\IAAS\Http\Requests\VmBackupHeatmapStats\VmBackupHeatmapStatsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VmBackupHeatmapStatsController extends AbstractController
{
    private $model = VmBackupHeatmapStats::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of vmbackupheatmapstats.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VmBackupHeatmapStatsQueryFilter $filter  An object that builds search query
     * @param  Request                         $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VmBackupHeatmapStatsQueryFilter $filter, Request $request)
    {
        $data = VmBackupHeatmapStatsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VmBackupHeatmapStatsService::getActions();

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
        $actionId = VmBackupHeatmapStatsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $vmBackupHeatmapStatsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VmBackupHeatmapStatsService::getByRef($ref);

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
        $objects = VmBackupHeatmapStatsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VmBackupHeatmapStats object on database.
     *
     * @param  VmBackupHeatmapStatsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VmBackupHeatmapStatsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VmBackupHeatmapStatsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VmBackupHeatmapStats object on database.
     *
     * @param  $vmBackupHeatmapStatsId
     * @param  VmBackupHeatmapStatsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($vmBackupHeatmapStatsId, VmBackupHeatmapStatsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VmBackupHeatmapStatsService::update($vmBackupHeatmapStatsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VmBackupHeatmapStats object on database.
     *
     * @param  $vmBackupHeatmapStatsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($vmBackupHeatmapStatsId)
    {
        $model = VmBackupHeatmapStatsService::delete($vmBackupHeatmapStatsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
