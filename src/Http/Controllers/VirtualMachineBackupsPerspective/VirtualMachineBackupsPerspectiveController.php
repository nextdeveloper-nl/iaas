<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineBackupsPerspective;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineBackupsPerspective\VirtualMachineBackupsPerspectiveUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineBackupsPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackupsPerspective;
use NextDeveloper\IAAS\Services\VirtualMachineBackupsPerspectiveService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineBackupsPerspective\VirtualMachineBackupsPerspectiveCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineBackupsPerspectiveController extends AbstractController
{
    private $model = VirtualMachineBackupsPerspective::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinebackupsperspectives.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineBackupsPerspectiveQueryFilter $filter  An object that builds search query
     * @param  Request                                     $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineBackupsPerspectiveQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineBackupsPerspectiveService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineBackupsPerspectiveService::getActions();

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
        $actionId = VirtualMachineBackupsPerspectiveService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineBackupsPerspectiveId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineBackupsPerspectiveService::getByRef($ref);

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
        $objects = VirtualMachineBackupsPerspectiveService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineBackupsPerspective object on database.
     *
     * @param  VirtualMachineBackupsPerspectiveCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineBackupsPerspectiveCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineBackupsPerspectiveService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineBackupsPerspective object on database.
     *
     * @param  $virtualMachineBackupsPerspectiveId
     * @param  VirtualMachineBackupsPerspectiveUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineBackupsPerspectiveId, VirtualMachineBackupsPerspectiveUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineBackupsPerspectiveService::update($virtualMachineBackupsPerspectiveId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineBackupsPerspective object on database.
     *
     * @param  $virtualMachineBackupsPerspectiveId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineBackupsPerspectiveId)
    {
        $model = VirtualMachineBackupsPerspectiveService::delete($virtualMachineBackupsPerspectiveId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
