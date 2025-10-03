<?php

namespace NextDeveloper\IAAS\Http\Controllers\BackupRetentionPolicies;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\BackupRetentionPolicies\BackupRetentionPoliciesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\BackupRetentionPoliciesQueryFilter;
use NextDeveloper\IAAS\Database\Models\BackupRetentionPolicies;
use NextDeveloper\IAAS\Services\BackupRetentionPoliciesService;
use NextDeveloper\IAAS\Http\Requests\BackupRetentionPolicies\BackupRetentionPoliciesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class BackupRetentionPoliciesController extends AbstractController
{
    private $model = BackupRetentionPolicies::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of backupretentionpolicies.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  BackupRetentionPoliciesQueryFilter $filter  An object that builds search query
     * @param  Request                            $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(BackupRetentionPoliciesQueryFilter $filter, Request $request)
    {
        $data = BackupRetentionPoliciesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = BackupRetentionPoliciesService::getActions();

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
        $actionId = BackupRetentionPoliciesService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $backupRetentionPoliciesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = BackupRetentionPoliciesService::getByRef($ref);

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
        $objects = BackupRetentionPoliciesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created BackupRetentionPolicies object on database.
     *
     * @param  BackupRetentionPoliciesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(BackupRetentionPoliciesCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = BackupRetentionPoliciesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates BackupRetentionPolicies object on database.
     *
     * @param  $backupRetentionPoliciesId
     * @param  BackupRetentionPoliciesUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($backupRetentionPoliciesId, BackupRetentionPoliciesUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = BackupRetentionPoliciesService::update($backupRetentionPoliciesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates BackupRetentionPolicies object on database.
     *
     * @param  $backupRetentionPoliciesId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($backupRetentionPoliciesId)
    {
        $model = BackupRetentionPoliciesService::delete($backupRetentionPoliciesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
