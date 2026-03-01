<?php

namespace NextDeveloper\IAAS\Http\Controllers\VmBackupHeatmapByClouds;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VmBackupHeatmapByClouds\VmBackupHeatmapByCloudsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VmBackupHeatmapByCloudsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmapByClouds;
use NextDeveloper\IAAS\Services\VmBackupHeatmapByCloudsService;
use NextDeveloper\IAAS\Http\Requests\VmBackupHeatmapByClouds\VmBackupHeatmapByCloudsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VmBackupHeatmapByCloudsController extends AbstractController
{
    private $model = VmBackupHeatmapByClouds::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of vmbackupheatmapbyclouds.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VmBackupHeatmapByCloudsQueryFilter $filter  An object that builds search query
     * @param  Request                            $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VmBackupHeatmapByCloudsQueryFilter $filter, Request $request)
    {
        $data = VmBackupHeatmapByCloudsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VmBackupHeatmapByCloudsService::getActions();

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
        $actionId = VmBackupHeatmapByCloudsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $vmBackupHeatmapByCloudsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VmBackupHeatmapByCloudsService::getByRef($ref);

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
        $objects = VmBackupHeatmapByCloudsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VmBackupHeatmapByClouds object on database.
     *
     * @param  VmBackupHeatmapByCloudsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VmBackupHeatmapByCloudsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VmBackupHeatmapByCloudsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VmBackupHeatmapByClouds object on database.
     *
     * @param  $vmBackupHeatmapByCloudsId
     * @param  VmBackupHeatmapByCloudsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($vmBackupHeatmapByCloudsId, VmBackupHeatmapByCloudsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VmBackupHeatmapByCloudsService::update($vmBackupHeatmapByCloudsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VmBackupHeatmapByClouds object on database.
     *
     * @param  $vmBackupHeatmapByCloudsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($vmBackupHeatmapByCloudsId)
    {
        $model = VmBackupHeatmapByCloudsService::delete($vmBackupHeatmapByCloudsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
