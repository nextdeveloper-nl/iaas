<?php

namespace NextDeveloper\IAAS\Http\Controllers\AnsibleSystemPlays;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlays\AnsibleSystemPlaysUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\AnsibleSystemPlaysQueryFilter;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlays;
use NextDeveloper\IAAS\Services\AnsibleSystemPlaysService;
use NextDeveloper\IAAS\Http\Requests\AnsibleSystemPlays\AnsibleSystemPlaysCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class AnsibleSystemPlaysController extends AbstractController
{
    private $model = AnsibleSystemPlays::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of ansiblesystemplays.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  AnsibleSystemPlaysQueryFilter $filter  An object that builds search query
     * @param  Request                       $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AnsibleSystemPlaysQueryFilter $filter, Request $request)
    {
        $data = AnsibleSystemPlaysService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = AnsibleSystemPlaysService::getActions();

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
        $actionId = AnsibleSystemPlaysService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ansibleSystemPlaysId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = AnsibleSystemPlaysService::getByRef($ref);

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
        $objects = AnsibleSystemPlaysService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created AnsibleSystemPlays object on database.
     *
     * @param  AnsibleSystemPlaysCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(AnsibleSystemPlaysCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsibleSystemPlaysService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsibleSystemPlays object on database.
     *
     * @param  $ansibleSystemPlaysId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($ansibleSystemPlaysId, AnsibleSystemPlaysUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = AnsibleSystemPlaysService::update($ansibleSystemPlaysId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates AnsibleSystemPlays object on database.
     *
     * @param  $ansibleSystemPlaysId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($ansibleSystemPlaysId)
    {
        $model = AnsibleSystemPlaysService::delete($ansibleSystemPlaysId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
