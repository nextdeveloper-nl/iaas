<?php

namespace NextDeveloper\IAAS\Actions\RepositoryImages;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Services\Repositories\RepositoryUpdateService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action will scan compute member and sync all findings
 */
class Delete extends AbstractAction
{
    public const EVENTS = [
        'deleting:NextDeveloper\IAAS\RepositoryImages',
        'deleted:NextDeveloper\IAAS\RepositoryImages',
    ];

    public const PARAMS = [];

    public function __construct(RepositoryImages $image, $params = null)
    {
        $this->model = $image;

        $this->queue = 'iaas';

        parent::__construct($params);
    }

    public function handle()
    {
        $this->setProgress(0, 'Deleting machine image: ' . $this->model->name);

        // Fire the event before deletion
        Events::fire('deleting:NextDeveloper\IAAS\RepositoryImages', $this->model);

        /**
         * This behaviour changed because we are now using soft images, instead of hard images.
         * We should make garbage collection for the images as well.
         */

//        // Directly delete the image from the repository
//        $isDeleted = $this->deleteImageFromServer();
//
//        if(!$isDeleted) {
//            // If deletion failed, set an error message
//            $this->setFinishedWithError('Failed to delete machine image from server.');
//            return;
//        }

        //  This means the image is deleted from the server, now we can delete the model
        $this->model->delete();

        // Fire the event after deletion
        Events::fire('deleted:NextDeveloper\IAAS\RepositoryImages', $this->model);

        //  @TODO: We should add a cleanup background process here.

        $this->setProgress(100, 'Machine image deleted.');
    }

    private function deleteImageFromServer() : bool
    {
        $repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->model->iaas_repository_id)
            ->first();

        if (!$repository) {
            $this->setFinished('Repository not found for the image.');
            return false;
        }

        $imagePath = $this->model->path;

        if(Str::contains($imagePath, ':/')) {
            // If the path is a local file, we can delete it directly
            $imagePath = str_replace(':/', '/', $imagePath); // Normalize the path
        }

        if(Str::contains($imagePath, '://')) {
            // If the path is a URL, we cannot delete it directly
            $this->setFinished('Cannot delete image from URL: ' . $imagePath);
            return false;
        }

        try {
            Log::debug(__METHOD__ . ' Deleting image from repository via SSH', [
                'command' => 'rm -f ' . escapeshellarg($imagePath),
                'repository_name' => $repository->name,
                'repository_id' => $repository->id,
                'image_path' => $imagePath,
            ]);

            $result = $repository->performSshCommand('rm -f ' . escapeshellarg($imagePath));
        } catch (CannotConnectWithSshException $e) {
            Log::error(__METHOD__ . ' The repository is not reachable via SSH: ' . $e->getMessage(), [
                'repository_name' => $repository->name,
                'repository_id' => $repository->id,
                'image_path' => $imagePath,
            ]);

            $this->setFinishedWithError('Failed to connect to repository via SSH: ' . $e->getMessage());
            return false;
        }

        if ($result['output'] !== 0) {
            $this->setFinishedWithError('Failed to delete image from repository: ' . $result['output']);
            return false;
        }

        return true;
    }
}
