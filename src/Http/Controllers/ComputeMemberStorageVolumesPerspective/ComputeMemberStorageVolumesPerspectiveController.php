<?php

namespace NextDeveloper\IAAS\Http\Controllers\ComputeMemberStorageVolumesPerspective;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\ComputeMemberStorageVolumesPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumesPerspective;
use NextDeveloper\IAAS\Services\ComputeMemberStorageVolumesPerspectiveService;
use NextDeveloper\IAAS\Http\Requests\ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class ComputeMemberStorageVolumesPerspectiveController extends AbstractController
{
    private $model = ComputeMemberStorageVolumesPerspective::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of computememberstoragevolumesperspectives.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  ComputeMemberStorageVolumesPerspectiveQueryFilter $filter  An object that builds search query
     * @param  Request                                           $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ComputeMemberStorageVolumesPerspectiveQueryFilter $filter, Request $request)
    {
        $data = ComputeMemberStorageVolumesPerspectiveService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = ComputeMemberStorageVolumesPerspectiveService::getActions();

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
        $actionId = ComputeMemberStorageVolumesPerspectiveService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $computeMemberStorageVolumesPerspectiveId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = ComputeMemberStorageVolumesPerspectiveService::getByRef($ref);

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
        $objects = ComputeMemberStorageVolumesPerspectiveService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created ComputeMemberStorageVolumesPerspective object on database.
     *
     * @param  ComputeMemberStorageVolumesPerspectiveCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(ComputeMemberStorageVolumesPerspectiveCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = ComputeMemberStorageVolumesPerspectiveService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputeMemberStorageVolumesPerspective object on database.
     *
     * @param  $computeMemberStorageVolumesPerspectiveId
     * @param  ComputeMemberStorageVolumesPerspectiveUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($computeMemberStorageVolumesPerspectiveId, ComputeMemberStorageVolumesPerspectiveUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = ComputeMemberStorageVolumesPerspectiveService::update($computeMemberStorageVolumesPerspectiveId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates ComputeMemberStorageVolumesPerspective object on database.
     *
     * @param  $computeMemberStorageVolumesPerspectiveId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($computeMemberStorageVolumesPerspectiveId)
    {
        $model = ComputeMemberStorageVolumesPerspectiveService::delete($computeMemberStorageVolumesPerspectiveId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
