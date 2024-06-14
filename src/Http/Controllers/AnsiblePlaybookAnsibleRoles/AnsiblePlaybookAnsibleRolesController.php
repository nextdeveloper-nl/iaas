<?php

namespace NextDeveloper\IAAS\Http\Controllers\AnsiblePlaybookAnsibleRoles;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookAnsibleRoles\AnsiblePlaybookAnsibleRolesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\AnsiblePlaybookAnsibleRolesQueryFilter;
use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookAnsibleRoles;
use NextDeveloper\IAAS\Services\AnsiblePlaybookAnsibleRolesService;
use NextDeveloper\IAAS\Http\Requests\AnsiblePlaybookAnsibleRoles\AnsiblePlaybookAnsibleRolesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class AnsiblePlaybookAnsibleRolesController extends AbstractController
{
    private $model = AnsiblePlaybookAnsibleRoles::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of ansibleplaybookansibleroles.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  AnsiblePlaybookAnsibleRolesQueryFilter $filter  An object that builds search query
     * @param  Request                                $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AnsiblePlaybookAnsibleRolesQueryFilter $filter, Request $request)
    {
        $data = AnsiblePlaybookAnsibleRolesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = AnsiblePlaybookAnsibleRolesService::getActions();

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
        $actionId = AnsiblePlaybookAnsibleRolesService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ansiblePlaybookAnsibleRolesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = AnsiblePlaybookAnsibleRolesService::getByRef($ref);

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
        $objects = AnsiblePlaybookAnsibleRolesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created AnsiblePlaybookAnsibleRoles object on database.
     *
     * @param  AnsiblePlaybookAnsibleRolesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(AnsiblePlaybookAnsibleRolesCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsiblePlaybookAnsibleRolesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsiblePlaybookAnsibleRoles object on database.
     *
     * @param  $ansiblePlaybookAnsibleRolesId
     * @param  AnsiblePlaybookAnsibleRolesUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($ansiblePlaybookAnsibleRolesId, AnsiblePlaybookAnsibleRolesUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsiblePlaybookAnsibleRolesService::update($ansiblePlaybookAnsibleRolesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsiblePlaybookAnsibleRoles object on database.
     *
     * @param  $ansiblePlaybookAnsibleRolesId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($ansiblePlaybookAnsibleRolesId)
    {
        $model = AnsiblePlaybookAnsibleRolesService::delete($ansiblePlaybookAnsibleRolesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
