<?php

namespace NextDeveloper\IAAS\Http\Controllers\StorageVolumes;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\StorageVolumes\StorageVolumesUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\StorageVolumesQueryFilter;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Services\StorageVolumesService;
use NextDeveloper\IAAS\Http\Requests\StorageVolumes\StorageVolumesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;
class StorageVolumesController extends AbstractController
{
    private $model = StorageVolumes::class;

    use Tags;
    /**
     * This method returns the list of storagevolumes.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  StorageVolumesQueryFilter $filter  An object that builds search query
     * @param  Request                   $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(StorageVolumesQueryFilter $filter, Request $request)
    {
        $data = StorageVolumesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $storageVolumesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = StorageVolumesService::getByRef($ref);

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
        $objects = StorageVolumesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created StorageVolumes object on database.
     *
     * @param  StorageVolumesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(StorageVolumesCreateRequest $request)
    {
        $model = StorageVolumesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates StorageVolumes object on database.
     *
     * @param  $storageVolumesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($storageVolumesId, StorageVolumesUpdateRequest $request)
    {
        $model = StorageVolumesService::update($storageVolumesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates StorageVolumes object on database.
     *
     * @param  $storageVolumesId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($storageVolumesId)
    {
        $model = StorageVolumesService::delete($storageVolumesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
