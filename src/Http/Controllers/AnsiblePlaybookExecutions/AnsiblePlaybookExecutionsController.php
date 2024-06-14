<?php

namespace NextDeveloper\IAAS\Http\Controllers\AnsiblePlaybookExecutions;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\AnsiblePlaybookExecutionsQueryFilter;
use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookExecutions;
use NextDeveloper\IAAS\Services\AnsiblePlaybookExecutionsService;
use NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class AnsiblePlaybookExecutionsController extends AbstractController
{
    private $model = AnsiblePlaybookExecutions::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of ansibleplaybookexecutions.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  AnsiblePlaybookExecutionsQueryFilter $filter  An object that builds search query
     * @param  Request                              $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AnsiblePlaybookExecutionsQueryFilter $filter, Request $request)
    {
        $data = AnsiblePlaybookExecutionsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = AnsiblePlaybookExecutionsService::getActions();

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
        $actionId = AnsiblePlaybookExecutionsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ansiblePlaybookExecutionsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = AnsiblePlaybookExecutionsService::getByRef($ref);

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
        $objects = AnsiblePlaybookExecutionsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created AnsiblePlaybookExecutions object on database.
     *
     * @param  AnsiblePlaybookExecutionsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(AnsiblePlaybookExecutionsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsiblePlaybookExecutionsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsiblePlaybookExecutions object on database.
     *
     * @param  $ansiblePlaybookExecutionsId
     * @param  AnsiblePlaybookExecutionsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($ansiblePlaybookExecutionsId, AnsiblePlaybookExecutionsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsiblePlaybookExecutionsService::update($ansiblePlaybookExecutionsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsiblePlaybookExecutions object on database.
     *
     * @param  $ansiblePlaybookExecutionsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($ansiblePlaybookExecutionsId)
    {
        $model = AnsiblePlaybookExecutionsService::delete($ansiblePlaybookExecutionsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
