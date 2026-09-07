<?php

namespace NextDeveloper\IAAS\Http\Controllers\DockerContainers;

use Illuminate\Http\Request;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\Commons\Http\Traits\Tags;
use NextDeveloper\Events\Exceptions\AgentTimeoutException;
use NextDeveloper\IAAS\Database\Filters\DockerContainersQueryFilter;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Http\Requests\DockerContainers\DockerContainersCreateRequest;
use NextDeveloper\IAAS\Http\Requests\DockerContainers\DockerContainersUpdateRequest;
use NextDeveloper\IAAS\Services\DockerContainersService;

/**
 * Note: a different, unrelated class also named DockerContainersController
 * exists at Http\Controllers\RepositoryImages\DockerContainersController - it
 * manages Docker *image* listings for a private VM-template registry and has
 * nothing to do with this one (container hosting on a VM's own agent). Same
 * class name, different namespace - do not confuse the two.
 */
class DockerContainersController extends AbstractController
{
    private $model = DockerContainers::class;

    use Tags;
    use Addresses;

    /**
     * This method returns the list of docker containers.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  DockerContainersQueryFilter $filter  An object that builds search query
     * @param  Request                     $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(DockerContainersQueryFilter $filter, Request $request)
    {
        $data = DockerContainersService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = DockerContainersService::getActions();

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
        $actionId = DockerContainersService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $ref
     * @return mixed|null
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = DockerContainersService::getByRef($ref);

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
        $objects = DockerContainersService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method creates a DockerContainers object on database and dispatches
     * the agent-side creation (see DockerContainersService::create()).
     *
     * @param  DockerContainersCreateRequest $request
     * @return mixed|null
     */
    public function store(DockerContainersCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = DockerContainersService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates DockerContainers object on database.
     *
     * @param  $dockerContainersId
     * @param  DockerContainersUpdateRequest $request
     * @return mixed|null
     */
    public function update($dockerContainersId, DockerContainersUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = DockerContainersService::update($dockerContainersId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method deletes the DockerContainers row. Use the "remove" action
     * (POST /{docker_container}/do/remove) instead to also remove the container
     * from its host's Docker Engine first - this endpoint only removes the
     * platform-side record.
     *
     * @param  $dockerContainersId
     * @return mixed|null
     */
    public function destroy($dockerContainersId)
    {
        $model = DockerContainersService::delete($dockerContainersId);

        return $this->noContent();
    }

    /**
     * Returns the tail of this container's combined stdout/stderr log, fetched
     * synchronously from the agent - read-only, so it bypasses the queued
     * Actions/doAction() machinery used for lifecycle verbs.
     *
     * @param  $ref
     * @return mixed|null
     */
    public function logs($ref)
    {
        $container = DockerContainersService::getByRef($ref);

        if (!$container || !$container->virtualMachine || !$container->container_id) {
            return $this->setStatusCode(404)->withError(
                'This container has not been created on its host yet, or its host VM could not be found.',
                'ERROR-CONTAINER-NOT-READY'
            );
        }

        try {
            $result = $container->virtualMachine->sendAgentCommandSync('docker.logs', [
                'container_id' => $container->container_id,
                'tail'         => (int) request()->input('tail', 200),
            ], 10);
        } catch (AgentTimeoutException $e) {
            return $this->setStatusCode(504)->withError($e->getMessage(), 'ERROR-AGENT-TIMEOUT');
        }

        return $this->withArray($result);
    }

    /**
     * Returns a point-in-time CPU/memory/network usage snapshot for this
     * container, fetched synchronously from the agent.
     *
     * @param  $ref
     * @return mixed|null
     */
    public function stats($ref)
    {
        $container = DockerContainersService::getByRef($ref);

        if (!$container || !$container->virtualMachine || !$container->container_id) {
            return $this->setStatusCode(404)->withError(
                'This container has not been created on its host yet, or its host VM could not be found.',
                'ERROR-CONTAINER-NOT-READY'
            );
        }

        try {
            $result = $container->virtualMachine->sendAgentCommandSync('docker.stats', [
                'container_id' => $container->container_id,
            ], 10);
        } catch (AgentTimeoutException $e) {
            return $this->setStatusCode(504)->withError($e->getMessage(), 'ERROR-AGENT-TIMEOUT');
        }

        return $this->withArray($result);
    }
}
