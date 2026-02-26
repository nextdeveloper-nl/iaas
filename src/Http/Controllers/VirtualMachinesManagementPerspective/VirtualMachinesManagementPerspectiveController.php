<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachinesManagementPerspective;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachinesManagementPerspective\VirtualMachinesManagementPerspectiveUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachinesManagementPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesManagementPerspective;
use NextDeveloper\IAAS\Services\VirtualMachinesManagementPerspectiveService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachinesManagementPerspective\VirtualMachinesManagementPerspectiveCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachinesManagementPerspectiveController extends AbstractController
{
    private $model = VirtualMachinesManagementPerspective::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinesmanagementperspectives.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachinesManagementPerspectiveQueryFilter $filter  An object that builds search query
     * @param  Request                                         $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachinesManagementPerspectiveQueryFilter $filter, Request $request)
    {
        $data = VirtualMachinesManagementPerspectiveService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachinesManagementPerspectiveService::getActions();

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
        $actionId = VirtualMachinesManagementPerspectiveService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachinesManagementPerspectiveId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachinesManagementPerspectiveService::getByRef($ref);

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
        $objects = VirtualMachinesManagementPerspectiveService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachinesManagementPerspective object on database.
     *
     * @param  VirtualMachinesManagementPerspectiveCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachinesManagementPerspectiveCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachinesManagementPerspectiveService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachinesManagementPerspective object on database.
     *
     * @param  $virtualMachinesManagementPerspectiveId
     * @param  VirtualMachinesManagementPerspectiveUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachinesManagementPerspectiveId, VirtualMachinesManagementPerspectiveUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachinesManagementPerspectiveService::update($virtualMachinesManagementPerspectiveId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachinesManagementPerspective object on database.
     *
     * @param  $virtualMachinesManagementPerspectiveId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachinesManagementPerspectiveId)
    {
        $model = VirtualMachinesManagementPerspectiveService::delete($virtualMachinesManagementPerspectiveId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
