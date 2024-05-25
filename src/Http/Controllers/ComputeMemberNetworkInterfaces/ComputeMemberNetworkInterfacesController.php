<?php

namespace NextDeveloper\IAAS\Http\Controllers\ComputeMemberNetworkInterfaces;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\ComputeMemberNetworkInterfacesQueryFilter;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Services\ComputeMemberNetworkInterfacesService;
use NextDeveloper\IAAS\Http\Requests\ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class ComputeMemberNetworkInterfacesController extends AbstractController
{
    private $model = ComputeMemberNetworkInterfaces::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of computemembernetworkinterfaces.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  ComputeMemberNetworkInterfacesQueryFilter $filter  An object that builds search query
     * @param  Request                                   $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ComputeMemberNetworkInterfacesQueryFilter $filter, Request $request)
    {
        $data = ComputeMemberNetworkInterfacesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = ComputeMemberNetworkInterfacesService::getActions();

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
        $actionId = ComputeMemberNetworkInterfacesService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $computeMemberNetworkInterfacesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = ComputeMemberNetworkInterfacesService::getByRef($ref);

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
        $objects = ComputeMemberNetworkInterfacesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created ComputeMemberNetworkInterfaces object on database.
     *
     * @param  ComputeMemberNetworkInterfacesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(ComputeMemberNetworkInterfacesCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = ComputeMemberNetworkInterfacesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputeMemberNetworkInterfaces object on database.
     *
     * @param  $computeMemberNetworkInterfacesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($computeMemberNetworkInterfacesId, ComputeMemberNetworkInterfacesUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = ComputeMemberNetworkInterfacesService::update($computeMemberNetworkInterfacesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputeMemberNetworkInterfaces object on database.
     *
     * @param  $computeMemberNetworkInterfacesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($computeMemberNetworkInterfacesId)
    {
        $model = ComputeMemberNetworkInterfacesService::delete($computeMemberNetworkInterfacesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
