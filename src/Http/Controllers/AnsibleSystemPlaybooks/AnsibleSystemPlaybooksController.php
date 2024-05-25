<?php

namespace NextDeveloper\IAAS\Http\Controllers\AnsibleSystemPlaybooks;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybooks\AnsibleSystemPlaybooksUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\AnsibleSystemPlaybooksQueryFilter;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybooks;
use NextDeveloper\IAAS\Services\AnsibleSystemPlaybooksService;
use NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlaybooks\AnsibleSystemPlaybooksCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class AnsibleSystemPlaybooksController extends AbstractController
{
    private $model = AnsibleSystemPlaybooks::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of ansiblesystemplaybooks.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  AnsibleSystemPlaybooksQueryFilter $filter  An object that builds search query
     * @param  Request                           $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AnsibleSystemPlaybooksQueryFilter $filter, Request $request)
    {
        $data = AnsibleSystemPlaybooksService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = AnsibleSystemPlaybooksService::getActions();

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
        $actionId = AnsibleSystemPlaybooksService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ansibleSystemPlaybooksId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = AnsibleSystemPlaybooksService::getByRef($ref);

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
        $objects = AnsibleSystemPlaybooksService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created AnsibleSystemPlaybooks object on database.
     *
     * @param  AnsibleSystemPlaybooksCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(AnsibleSystemPlaybooksCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsibleSystemPlaybooksService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsibleSystemPlaybooks object on database.
     *
     * @param  $ansibleSystemPlaybooksId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($ansibleSystemPlaybooksId, AnsibleSystemPlaybooksUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsibleSystemPlaybooksService::update($ansibleSystemPlaybooksId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsibleSystemPlaybooks object on database.
     *
     * @param  $ansibleSystemPlaybooksId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($ansibleSystemPlaybooksId)
    {
        $model = AnsibleSystemPlaybooksService::delete($ansibleSystemPlaybooksId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
