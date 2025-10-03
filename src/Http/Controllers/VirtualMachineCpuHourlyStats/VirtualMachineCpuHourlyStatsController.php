<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineCpuHourlyStats;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuHourlyStats\VirtualMachineCpuHourlyStatsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineCpuHourlyStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachineCpuHourlyStats;
use NextDeveloper\IAAS\Services\VirtualMachineCpuHourlyStatsService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuHourlyStats\VirtualMachineCpuHourlyStatsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineCpuHourlyStatsController extends AbstractController
{
    private $model = VirtualMachineCpuHourlyStats::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinecpuhourlystats.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineCpuHourlyStatsQueryFilter $filter  An object that builds search query
     * @param  Request                                 $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineCpuHourlyStatsQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineCpuHourlyStatsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineCpuHourlyStatsService::getActions();

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
        $actionId = VirtualMachineCpuHourlyStatsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineCpuHourlyStatsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineCpuHourlyStatsService::getByRef($ref);

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
        $objects = VirtualMachineCpuHourlyStatsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineCpuHourlyStats object on database.
     *
     * @param  VirtualMachineCpuHourlyStatsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineCpuHourlyStatsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineCpuHourlyStatsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineCpuHourlyStats object on database.
     *
     * @param  $virtualMachineCpuHourlyStatsId
     * @param  VirtualMachineCpuHourlyStatsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineCpuHourlyStatsId, VirtualMachineCpuHourlyStatsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineCpuHourlyStatsService::update($virtualMachineCpuHourlyStatsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineCpuHourlyStats object on database.
     *
     * @param  $virtualMachineCpuHourlyStatsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineCpuHourlyStatsId)
    {
        $model = VirtualMachineCpuHourlyStatsService::delete($virtualMachineCpuHourlyStatsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
