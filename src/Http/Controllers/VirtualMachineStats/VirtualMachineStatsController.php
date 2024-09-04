<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineStats;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineStats\VirtualMachineStatsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachineStats;
use NextDeveloper\IAAS\Services\VirtualMachineStatsService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineStats\VirtualMachineStatsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineStatsController extends AbstractController
{
    private $model = VirtualMachineStats::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinestats.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineStatsQueryFilter $filter  An object that builds search query
     * @param  Request                        $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineStatsQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineStatsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineStatsService::getActions();

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
        $actionId = VirtualMachineStatsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineStatsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineStatsService::getByRef($ref);

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
        $objects = VirtualMachineStatsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineStats object on database.
     *
     * @param  VirtualMachineStatsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineStatsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineStatsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineStats object on database.
     *
     * @param  $virtualMachineStatsId
     * @param  VirtualMachineStatsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineStatsId, VirtualMachineStatsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineStatsService::update($virtualMachineStatsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineStats object on database.
     *
     * @param  $virtualMachineStatsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineStatsId)
    {
        $model = VirtualMachineStatsService::delete($virtualMachineStatsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
