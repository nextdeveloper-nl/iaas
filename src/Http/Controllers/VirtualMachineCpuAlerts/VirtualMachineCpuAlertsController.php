<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineCpuAlerts;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuAlerts\VirtualMachineCpuAlertsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineCpuAlertsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachineCpuAlerts;
use NextDeveloper\IAAS\Services\VirtualMachineCpuAlertsService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuAlerts\VirtualMachineCpuAlertsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineCpuAlertsController extends AbstractController
{
    private $model = VirtualMachineCpuAlerts::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinecpualerts.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineCpuAlertsQueryFilter $filter  An object that builds search query
     * @param  Request                            $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineCpuAlertsQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineCpuAlertsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineCpuAlertsService::getActions();

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
        $actionId = VirtualMachineCpuAlertsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineCpuAlertsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineCpuAlertsService::getByRef($ref);

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
        $objects = VirtualMachineCpuAlertsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineCpuAlerts object on database.
     *
     * @param  VirtualMachineCpuAlertsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineCpuAlertsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineCpuAlertsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineCpuAlerts object on database.
     *
     * @param  $virtualMachineCpuAlertsId
     * @param  VirtualMachineCpuAlertsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineCpuAlertsId, VirtualMachineCpuAlertsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineCpuAlertsService::update($virtualMachineCpuAlertsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineCpuAlerts object on database.
     *
     * @param  $virtualMachineCpuAlertsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineCpuAlertsId)
    {
        $model = VirtualMachineCpuAlertsService::delete($virtualMachineCpuAlertsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
