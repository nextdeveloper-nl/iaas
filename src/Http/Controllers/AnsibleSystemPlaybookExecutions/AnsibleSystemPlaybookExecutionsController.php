<?php

namespace NextDeveloper\IAAS\Http\Controllers\AnsibleSystemPlaybookExecutions;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\AnsibleSystemPlaybookExecutionsQueryFilter;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybookExecutions;
use NextDeveloper\IAAS\Services\AnsibleSystemPlaybookExecutionsService;
use NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class AnsibleSystemPlaybookExecutionsController extends AbstractController
{
    private $model = AnsibleSystemPlaybookExecutions::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of ansiblesystemplaybookexecutions.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  AnsibleSystemPlaybookExecutionsQueryFilter $filter  An object that builds search query
     * @param  Request                                    $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AnsibleSystemPlaybookExecutionsQueryFilter $filter, Request $request)
    {
        $data = AnsibleSystemPlaybookExecutionsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = AnsibleSystemPlaybookExecutionsService::getActions();

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
        $actionId = AnsibleSystemPlaybookExecutionsService::doAction($objectId, $action);

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ansibleSystemPlaybookExecutionsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = AnsibleSystemPlaybookExecutionsService::getByRef($ref);

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
        $objects = AnsibleSystemPlaybookExecutionsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created AnsibleSystemPlaybookExecutions object on database.
     *
     * @param  AnsibleSystemPlaybookExecutionsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(AnsibleSystemPlaybookExecutionsCreateRequest $request)
    {
        $model = AnsibleSystemPlaybookExecutionsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsibleSystemPlaybookExecutions object on database.
     *
     * @param  $ansibleSystemPlaybookExecutionsId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($ansibleSystemPlaybookExecutionsId, AnsibleSystemPlaybookExecutionsUpdateRequest $request)
    {
        $model = AnsibleSystemPlaybookExecutionsService::update($ansibleSystemPlaybookExecutionsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsibleSystemPlaybookExecutions object on database.
     *
     * @param  $ansibleSystemPlaybookExecutionsId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($ansibleSystemPlaybookExecutionsId)
    {
        $model = AnsibleSystemPlaybookExecutionsService::delete($ansibleSystemPlaybookExecutionsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
