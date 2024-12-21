<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualDiskImageStats;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualDiskImageStats\VirtualDiskImageStatsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualDiskImageStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImageStats;
use NextDeveloper\IAAS\Services\VirtualDiskImageStatsService;
use NextDeveloper\IAAS\Http\Requests\VirtualDiskImageStats\VirtualDiskImageStatsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualDiskImageStatsController extends AbstractController
{
    private $model = VirtualDiskImageStats::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualdiskimagestats.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualDiskImageStatsQueryFilter $filter  An object that builds search query
     * @param  Request                          $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualDiskImageStatsQueryFilter $filter, Request $request)
    {
        $data = VirtualDiskImageStatsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualDiskImageStatsService::getActions();

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
        $actionId = VirtualDiskImageStatsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualDiskImageStatsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualDiskImageStatsService::getByRef($ref);

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
        $objects = VirtualDiskImageStatsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualDiskImageStats object on database.
     *
     * @param  VirtualDiskImageStatsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualDiskImageStatsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualDiskImageStatsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualDiskImageStats object on database.
     *
     * @param  $virtualDiskImageStatsId
     * @param  VirtualDiskImageStatsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualDiskImageStatsId, VirtualDiskImageStatsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualDiskImageStatsService::update($virtualDiskImageStatsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualDiskImageStats object on database.
     *
     * @param  $virtualDiskImageStatsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualDiskImageStatsId)
    {
        $model = VirtualDiskImageStatsService::delete($virtualDiskImageStatsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
