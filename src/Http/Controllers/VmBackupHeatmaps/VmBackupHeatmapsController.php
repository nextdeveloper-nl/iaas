<?php

namespace NextDeveloper\IAAS\Http\Controllers\VmBackupHeatmaps;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VmBackupHeatmaps\VmBackupHeatmapsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VmBackupHeatmapsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmaps;
use NextDeveloper\IAAS\Services\VmBackupHeatmapsService;
use NextDeveloper\IAAS\Http\Requests\VmBackupHeatmaps\VmBackupHeatmapsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VmBackupHeatmapsController extends AbstractController
{
    private $model = VmBackupHeatmaps::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of vmbackupheatmaps.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VmBackupHeatmapsQueryFilter $filter  An object that builds search query
     * @param  Request                     $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VmBackupHeatmapsQueryFilter $filter, Request $request)
    {
        $data = VmBackupHeatmapsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VmBackupHeatmapsService::getActions();

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
        $actionId = VmBackupHeatmapsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $vmBackupHeatmapsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VmBackupHeatmapsService::getByRef($ref);

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
        $objects = VmBackupHeatmapsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VmBackupHeatmaps object on database.
     *
     * @param  VmBackupHeatmapsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VmBackupHeatmapsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VmBackupHeatmapsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VmBackupHeatmaps object on database.
     *
     * @param  $vmBackupHeatmapsId
     * @param  VmBackupHeatmapsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($vmBackupHeatmapsId, VmBackupHeatmapsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VmBackupHeatmapsService::update($vmBackupHeatmapsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VmBackupHeatmaps object on database.
     *
     * @param  $vmBackupHeatmapsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($vmBackupHeatmapsId)
    {
        $model = VmBackupHeatmapsService::delete($vmBackupHeatmapsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
