<?php

namespace NextDeveloper\IAAS\Http\Controllers\NetworkPoolsPerspective;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\NetworkPoolsPerspective\NetworkPoolsPerspectiveUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\NetworkPoolsPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\NetworkPoolsPerspective;
use NextDeveloper\IAAS\Services\NetworkPoolsPerspectiveService;
use NextDeveloper\IAAS\Http\Requests\NetworkPoolsPerspective\NetworkPoolsPerspectiveCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
class NetworkPoolsPerspectiveController extends AbstractController
{
    private $model = NetworkPoolsPerspective::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of networkpoolsperspectives.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  NetworkPoolsPerspectiveQueryFilter $filter  An object that builds search query
     * @param  Request                            $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(NetworkPoolsPerspectiveQueryFilter $filter, Request $request)
    {
        $data = NetworkPoolsPerspectiveService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = NetworkPoolsPerspectiveService::getActions();

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
        $actionId = NetworkPoolsPerspectiveService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $networkPoolsPerspectiveId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = NetworkPoolsPerspectiveService::getByRef($ref);

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
        $objects = NetworkPoolsPerspectiveService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created NetworkPoolsPerspective object on database.
     *
     * @param  NetworkPoolsPerspectiveCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(NetworkPoolsPerspectiveCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = NetworkPoolsPerspectiveService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates NetworkPoolsPerspective object on database.
     *
     * @param  $networkPoolsPerspectiveId
     * @param  NetworkPoolsPerspectiveUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($networkPoolsPerspectiveId, NetworkPoolsPerspectiveUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = NetworkPoolsPerspectiveService::update($networkPoolsPerspectiveId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates NetworkPoolsPerspective object on database.
     *
     * @param  $networkPoolsPerspectiveId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($networkPoolsPerspectiveId)
    {
        $model = NetworkPoolsPerspectiveService::delete($networkPoolsPerspectiveId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
