<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineCpuMetricsAggs;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuMetricsAggs\VirtualMachineCpuMetricsAggsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineCpuMetricsAggsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachineCpuMetricsAggs;
use NextDeveloper\IAAS\Services\VirtualMachineCpuMetricsAggsService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineCpuMetricsAggs\VirtualMachineCpuMetricsAggsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineCpuMetricsAggsController extends AbstractController
{
    private $model = VirtualMachineCpuMetricsAggs::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinecpumetricsaggs.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineCpuMetricsAggsQueryFilter $filter  An object that builds search query
     * @param  Request                                 $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineCpuMetricsAggsQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineCpuMetricsAggsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineCpuMetricsAggsService::getActions();

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
        $actionId = VirtualMachineCpuMetricsAggsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineCpuMetricsAggsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineCpuMetricsAggsService::getByRef($ref);

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
        $objects = VirtualMachineCpuMetricsAggsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineCpuMetricsAggs object on database.
     *
     * @param  VirtualMachineCpuMetricsAggsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineCpuMetricsAggsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineCpuMetricsAggsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineCpuMetricsAggs object on database.
     *
     * @param  $virtualMachineCpuMetricsAggsId
     * @param  VirtualMachineCpuMetricsAggsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineCpuMetricsAggsId, VirtualMachineCpuMetricsAggsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineCpuMetricsAggsService::update($virtualMachineCpuMetricsAggsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineCpuMetricsAggs object on database.
     *
     * @param  $virtualMachineCpuMetricsAggsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineCpuMetricsAggsId)
    {
        $model = VirtualMachineCpuMetricsAggsService::delete($virtualMachineCpuMetricsAggsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
