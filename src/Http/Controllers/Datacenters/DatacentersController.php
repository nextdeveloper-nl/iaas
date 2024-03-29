<?php

namespace NextDeveloper\IAAS\Http\Controllers\Datacenters;

use Illuminate\Http\Request;
use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\Datacenters\DatacentersUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\DatacentersQueryFilter;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Services\DatacentersService;
use NextDeveloper\IAAS\Http\Requests\Datacenters\DatacentersCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;
class DatacentersController extends AbstractController
{
    private $model = Datacenters::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of datacenters.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  DatacentersQueryFilter $filter  An object that builds search query
     * @param  Request                $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(DatacentersQueryFilter $filter, Request $request)
    {
        $data = DatacentersService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $datacentersId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = DatacentersService::getByRef($ref);

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
        $objects = DatacentersService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created Datacenters object on database.
     *
     * @param  DatacentersCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(DatacentersCreateRequest $request)
    {
        $model = DatacentersService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates Datacenters object on database.
     *
     * @param  $datacentersId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($datacentersId, DatacentersUpdateRequest $request)
    {
        $model = DatacentersService::update($datacentersId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates Datacenters object on database.
     *
     * @param  $datacentersId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($datacentersId)
    {
        $model = DatacentersService::delete($datacentersId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
