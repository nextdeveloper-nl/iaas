<?php

namespace NextDeveloper\IAAS\Http\Controllers\StoragePools;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Generator\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\StoragePools\StoragePoolsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\StoragePoolsQueryFilter;
use NextDeveloper\IAAS\Services\StoragePoolsService;
use NextDeveloper\IAAS\Http\Requests\StoragePools\StoragePoolsCreateRequest;

class StoragePoolsController extends AbstractController
{
    /**
    * This method returns the list of storagepools.
    *
    * optional http params:
    * - paginate: If you set paginate parameter, the result will be returned paginated.
    *
    * @param StoragePoolsQueryFilter $filter An object that builds search query
    * @param Request $request Laravel request object, this holds all data about request. Automatically populated.
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(StoragePoolsQueryFilter $filter, Request $request) {
        $data = StoragePoolsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
    * This method receives ID for the related model and returns the item to the client.
    *
    * @param $storagePoolsId
    * @return mixed|null
    * @throws \Laravel\Octane\Exceptions\DdException
    */
    public function show($ref) {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = StoragePoolsService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method created StoragePools object on database.
    *
    * @param StoragePoolsCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function store(StoragePoolsCreateRequest $request) {
        $model = StoragePoolsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates StoragePools object on database.
    *
    * @param $storagePoolsId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function update($storagePoolsId, StoragePoolsUpdateRequest $request) {
        $model = StoragePoolsService::update($storagePoolsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
    * This method updates StoragePools object on database.
    *
    * @param $storagePoolsId
    * @param CountryCreateRequest $request
    * @return mixed|null
    * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
    */
    public function destroy($storagePoolsId) {
        $model = StoragePoolsService::delete($storagePoolsId);

        return ResponsableFactory::makeResponse($this, $model);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}