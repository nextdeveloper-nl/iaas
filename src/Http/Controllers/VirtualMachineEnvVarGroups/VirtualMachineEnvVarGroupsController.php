<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineEnvVarGroups;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineEnvVarGroups\VirtualMachineEnvVarGroupsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineEnvVarGroupsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachineEnvVarGroups;
use NextDeveloper\IAAS\Services\VirtualMachineEnvVarGroupsService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineEnvVarGroups\VirtualMachineEnvVarGroupsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineEnvVarGroupsController extends AbstractController
{
    private $model = VirtualMachineEnvVarGroups::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachineenvvargroups.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineEnvVarGroupsQueryFilter $filter  An object that builds search query
     * @param  Request                               $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineEnvVarGroupsQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineEnvVarGroupsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineEnvVarGroupsService::getActions();

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
        $actionId = VirtualMachineEnvVarGroupsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineEnvVarGroupsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineEnvVarGroupsService::getByRef($ref);

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
        $objects = VirtualMachineEnvVarGroupsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineEnvVarGroups object on database.
     *
     * @param  VirtualMachineEnvVarGroupsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineEnvVarGroupsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineEnvVarGroupsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineEnvVarGroups object on database.
     *
     * @param  $virtualMachineEnvVarGroupsId
     * @param  VirtualMachineEnvVarGroupsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineEnvVarGroupsId, VirtualMachineEnvVarGroupsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineEnvVarGroupsService::update($virtualMachineEnvVarGroupsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineEnvVarGroups object on database.
     *
     * @param  $virtualMachineEnvVarGroupsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineEnvVarGroupsId)
    {
        $model = VirtualMachineEnvVarGroupsService::delete($virtualMachineEnvVarGroupsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
