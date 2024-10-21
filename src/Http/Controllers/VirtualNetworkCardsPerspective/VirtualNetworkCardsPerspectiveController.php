<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualNetworkCardsPerspective;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualNetworkCardsPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCardsPerspective;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsPerspectiveService;
use NextDeveloper\IAAS\Http\Requests\VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualNetworkCardsPerspectiveController extends AbstractController
{
    private $model = VirtualNetworkCardsPerspective::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualnetworkcardsperspectives.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualNetworkCardsPerspectiveQueryFilter $filter  An object that builds search query
     * @param  Request                                   $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualNetworkCardsPerspectiveQueryFilter $filter, Request $request)
    {
        $data = VirtualNetworkCardsPerspectiveService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualNetworkCardsPerspectiveService::getActions();

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
        $actionId = VirtualNetworkCardsPerspectiveService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualNetworkCardsPerspectiveId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualNetworkCardsPerspectiveService::getByRef($ref);

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
        $objects = VirtualNetworkCardsPerspectiveService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualNetworkCardsPerspective object on database.
     *
     * @param  VirtualNetworkCardsPerspectiveCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualNetworkCardsPerspectiveCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualNetworkCardsPerspectiveService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualNetworkCardsPerspective object on database.
     *
     * @param  $virtualNetworkCardsPerspectiveId
     * @param  VirtualNetworkCardsPerspectiveUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualNetworkCardsPerspectiveId, VirtualNetworkCardsPerspectiveUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualNetworkCardsPerspectiveService::update($virtualNetworkCardsPerspectiveId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualNetworkCardsPerspective object on database.
     *
     * @param  $virtualNetworkCardsPerspectiveId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualNetworkCardsPerspectiveId)
    {
        $model = VirtualNetworkCardsPerspectiveService::delete($virtualNetworkCardsPerspectiveId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
