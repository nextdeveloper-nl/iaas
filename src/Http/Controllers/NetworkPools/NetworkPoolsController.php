<?php

namespace NextDeveloper\IAAS\Http\Controllers\NetworkPools;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Traits\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\NetworkPools\NetworkPoolsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\NetworkPoolsQueryFilter;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Services\NetworkPoolsService;
use NextDeveloper\IAAS\Http\Requests\NetworkPools\NetworkPoolsCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;
class NetworkPoolsController extends AbstractController
{
    private $model = NetworkPools::class;

    use Tags;
    /**
     * This method returns the list of networkpools.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  NetworkPoolsQueryFilter $filter  An object that builds search query
     * @param  Request                 $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(NetworkPoolsQueryFilter $filter, Request $request)
    {
        $data = NetworkPoolsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $networkPoolsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = NetworkPoolsService::getByRef($ref);

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
        $objects = NetworkPoolsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created NetworkPools object on database.
     *
     * @param  NetworkPoolsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(NetworkPoolsCreateRequest $request)
    {
        $model = NetworkPoolsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates NetworkPools object on database.
     *
     * @param  $networkPoolsId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($networkPoolsId, NetworkPoolsUpdateRequest $request)
    {
        $model = NetworkPoolsService::update($networkPoolsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates NetworkPools object on database.
     *
     * @param  $networkPoolsId
     * @param  CountryCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($networkPoolsId)
    {
        $model = NetworkPoolsService::delete($networkPoolsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
