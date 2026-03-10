<?php

namespace NextDeveloper\IAAS\Http\Controllers\EnvVarGroupVars;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\EnvVarGroupVars\EnvVarGroupVarsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\EnvVarGroupVarsQueryFilter;
use NextDeveloper\IAAS\Database\Models\EnvVarGroupVars;
use NextDeveloper\IAAS\Services\EnvVarGroupVarsService;
use NextDeveloper\IAAS\Http\Requests\EnvVarGroupVars\EnvVarGroupVarsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class EnvVarGroupVarsController extends AbstractController
{
    private $model = EnvVarGroupVars::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of envvargroupvars.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  EnvVarGroupVarsQueryFilter $filter  An object that builds search query
     * @param  Request                    $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(EnvVarGroupVarsQueryFilter $filter, Request $request)
    {
        $data = EnvVarGroupVarsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = EnvVarGroupVarsService::getActions();

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
        $actionId = EnvVarGroupVarsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $envVarGroupVarsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = EnvVarGroupVarsService::getByRef($ref);

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
        $objects = EnvVarGroupVarsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created EnvVarGroupVars object on database.
     *
     * @param  EnvVarGroupVarsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(EnvVarGroupVarsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = EnvVarGroupVarsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates EnvVarGroupVars object on database.
     *
     * @param  $envVarGroupVarsId
     * @param  EnvVarGroupVarsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($envVarGroupVarsId, EnvVarGroupVarsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = EnvVarGroupVarsService::update($envVarGroupVarsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates EnvVarGroupVars object on database.
     *
     * @param  $envVarGroupVarsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($envVarGroupVarsId)
    {
        $model = EnvVarGroupVarsService::delete($envVarGroupVarsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
