<?php

namespace NextDeveloper\IAAS\Http\Controllers\StorageVolumesPerspective;

use Illuminate\Http\Request;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;
use NextDeveloper\IAAS\Database\Filters\StorageVolumesPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\StorageVolumesPerspective;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Http\Requests\StorageVolumesPerspective\StorageVolumesPerspectiveCreateRequest;
use NextDeveloper\IAAS\Http\Requests\StorageVolumesPerspective\StorageVolumesPerspectiveUpdateRequest;
use NextDeveloper\IAAS\Services\StorageVolumesPerspectiveService;

class StorageVolumesPerspectiveController extends AbstractController
{
    private $model = StorageVolumesPerspective::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of storagevolumesperspectives.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  StorageVolumesPerspectiveQueryFilter $filter  An object that builds search query
     * @param  Request                              $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(StorageVolumesPerspectiveQueryFilter $filter, Request $request)
    {
        $data = StorageVolumesPerspectiveService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = StorageVolumesPerspectiveService::getActions();

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
        $actionId = StorageVolumesPerspectiveService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $storageVolumesPerspectiveId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = StorageVolumesPerspectiveService::getByRef($ref);

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
        $objects = StorageVolumesPerspectiveService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created StorageVolumesPerspective object on database.
     *
     * @param  StorageVolumesPerspectiveCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(StorageVolumesPerspectiveCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = StorageVolumesPerspectiveService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates StorageVolumesPerspective object on database.
     *
     * @param  $storageVolumesPerspectiveId
     * @param  StorageVolumesPerspectiveUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($storageVolumesPerspectiveId, StorageVolumesPerspectiveUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = StorageVolumesPerspectiveService::update($storageVolumesPerspectiveId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates StorageVolumesPerspective object on database.
     *
     * @param  $storageVolumesPerspectiveId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($storageVolumesPerspectiveId)
    {
        $model = StorageVolumesPerspectiveService::delete($storageVolumesPerspectiveId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
