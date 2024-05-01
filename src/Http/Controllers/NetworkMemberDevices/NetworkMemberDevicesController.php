<?php

namespace NextDeveloper\IAAS\Http\Controllers\NetworkMemberDevices;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\NetworkMemberDevices\NetworkMemberDevicesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\NetworkMemberDevicesQueryFilter;
use NextDeveloper\IAAS\Database\Models\NetworkMemberDevices;
use NextDeveloper\IAAS\Services\NetworkMemberDevicesService;
use NextDeveloper\IAAS\Http\Requests\NetworkMemberDevices\NetworkMemberDevicesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class NetworkMemberDevicesController extends AbstractController
{
    private $model = NetworkMemberDevices::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of networkmemberdevices.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  NetworkMemberDevicesQueryFilter $filter  An object that builds search query
     * @param  Request                         $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(NetworkMemberDevicesQueryFilter $filter, Request $request)
    {
        $data = NetworkMemberDevicesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = NetworkMemberDevicesService::getActions();

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
        $actionId = NetworkMemberDevicesService::doAction($objectId, $action);

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $networkMemberDevicesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = NetworkMemberDevicesService::getByRef($ref);

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
        $objects = NetworkMemberDevicesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created NetworkMemberDevices object on database.
     *
     * @param  NetworkMemberDevicesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(NetworkMemberDevicesCreateRequest $request)
    {
        $model = NetworkMemberDevicesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates NetworkMemberDevices object on database.
     *
     * @param  $networkMemberDevicesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($networkMemberDevicesId, NetworkMemberDevicesUpdateRequest $request)
    {
        $model = NetworkMemberDevicesService::update($networkMemberDevicesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates NetworkMemberDevices object on database.
     *
     * @param  $networkMemberDevicesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($networkMemberDevicesId)
    {
        $model = NetworkMemberDevicesService::delete($networkMemberDevicesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
