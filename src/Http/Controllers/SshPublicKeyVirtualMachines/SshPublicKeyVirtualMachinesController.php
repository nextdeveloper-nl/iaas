<?php

namespace NextDeveloper\IAAS\Http\Controllers\SshPublicKeyVirtualMachines;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\SshPublicKeyVirtualMachines\SshPublicKeyVirtualMachinesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\SshPublicKeyVirtualMachinesQueryFilter;
use NextDeveloper\IAAS\Database\Models\SshPublicKeyVirtualMachines;
use NextDeveloper\IAAS\Services\SshPublicKeyVirtualMachinesService;
use NextDeveloper\IAAS\Http\Requests\SshPublicKeyVirtualMachines\SshPublicKeyVirtualMachinesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class SshPublicKeyVirtualMachinesController extends AbstractController
{
    private $model = SshPublicKeyVirtualMachines::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of sshpublickeyvirtualmachines.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  SshPublicKeyVirtualMachinesQueryFilter $filter  An object that builds search query
     * @param  Request                                $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SshPublicKeyVirtualMachinesQueryFilter $filter, Request $request)
    {
        $data = SshPublicKeyVirtualMachinesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = SshPublicKeyVirtualMachinesService::getActions();

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
        $actionId = SshPublicKeyVirtualMachinesService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $sshPublicKeyVirtualMachinesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = SshPublicKeyVirtualMachinesService::getByRef($ref);

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
        $objects = SshPublicKeyVirtualMachinesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created SshPublicKeyVirtualMachines object on database.
     *
     * @param  SshPublicKeyVirtualMachinesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(SshPublicKeyVirtualMachinesCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = SshPublicKeyVirtualMachinesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates SshPublicKeyVirtualMachines object on database.
     *
     * @param  $sshPublicKeyVirtualMachinesId
     * @param  SshPublicKeyVirtualMachinesUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($sshPublicKeyVirtualMachinesId, SshPublicKeyVirtualMachinesUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = SshPublicKeyVirtualMachinesService::update($sshPublicKeyVirtualMachinesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates SshPublicKeyVirtualMachines object on database.
     *
     * @param  $sshPublicKeyVirtualMachinesId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($sshPublicKeyVirtualMachinesId)
    {
        $model = SshPublicKeyVirtualMachinesService::delete($sshPublicKeyVirtualMachinesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
