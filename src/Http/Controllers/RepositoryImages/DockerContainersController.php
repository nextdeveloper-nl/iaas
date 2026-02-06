<?php

namespace NextDeveloper\IAAS\Http\Controllers\RepositoryImages;

use App\Helpers\Http\ResponseHelper;
use Illuminate\Http\Request;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\Commons\Http\Traits\Tags;
use NextDeveloper\IAAS\Database\Filters\RepositoryImagesQueryFilter;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Http\Requests\RepositoryImages\RepositoryImagesCreateRequest;
use NextDeveloper\IAAS\Http\Requests\RepositoryImages\RepositoryImagesUpdateRequest;
use NextDeveloper\IAAS\Services\Repositories\DockerRegistryService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;

class DockerContainersController extends AbstractController
{
    private $model = RepositoryImages::class;

    use Tags;
    use Addresses;

    /**
     * This method returns the list of images in the docker container
     *
     * @param $repository
     * @return array
     */
    public function index($repository)
    {
        $repository = Repositories::where('uuid', $repository)->first();

        return ResponseHelper::data(
            DockerRegistryService::getDockerImages($repository)
        );
    }

    /**
     * This method returns the tags of the related repository
     *
     * @param $repository
     * @param $image
     * @return array
     */
    public function tagsIndex($repository, $image)
    {
        $repository = Repositories::where('uuid', $repository)->first();

        return ResponseHelper::data(
            DockerRegistryService::getDockerImageTags($repository, $image)
        );
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = RepositoryImagesService::getActions();

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
        $actionId = RepositoryImagesService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $repositoryImagesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = RepositoryImagesService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates RepositoryImages object on database.
     *
     * @param  $repositoryImagesId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($repository, $image, $tag)
    {
        $repository = Repositories::where('uuid', $repository)->first();

        DockerRegistryService::deleteDockerImage($repository, $image, $tag);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
