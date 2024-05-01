<?php

namespace NextDeveloper\IAAS\Http\Controllers\IpAddressHistories;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\IpAddressHistories\IpAddressHistoriesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\IpAddressHistoriesQueryFilter;
use NextDeveloper\IAAS\Database\Models\IpAddressHistories;
use NextDeveloper\IAAS\Services\IpAddressHistoriesService;
use NextDeveloper\IAAS\Http\Requests\IpAddressHistories\IpAddressHistoriesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class IpAddressHistoriesController extends AbstractController
{
    private $model = IpAddressHistories::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of ipaddresshistories.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  IpAddressHistoriesQueryFilter $filter  An object that builds search query
     * @param  Request                       $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IpAddressHistoriesQueryFilter $filter, Request $request)
    {
        $data = IpAddressHistoriesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = IpAddressHistoriesService::getActions();

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
        $actionId = IpAddressHistoriesService::doAction($objectId, $action);

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ipAddressHistoriesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = IpAddressHistoriesService::getByRef($ref);

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
        $objects = IpAddressHistoriesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created IpAddressHistories object on database.
     *
     * @param  IpAddressHistoriesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(IpAddressHistoriesCreateRequest $request)
    {
        $model = IpAddressHistoriesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates IpAddressHistories object on database.
     *
     * @param  $ipAddressHistoriesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($ipAddressHistoriesId, IpAddressHistoriesUpdateRequest $request)
    {
        $model = IpAddressHistoriesService::update($ipAddressHistoriesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates IpAddressHistories object on database.
     *
     * @param  $ipAddressHistoriesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($ipAddressHistoriesId)
    {
        $model = IpAddressHistoriesService::delete($ipAddressHistoriesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
