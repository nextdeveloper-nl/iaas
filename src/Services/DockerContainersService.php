<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Actions\DockerContainers\Create;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractDockerContainersService;

/**
 * This class is responsible from managing the data for DockerContainers
 *
 * Class DockerContainersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class DockerContainersService extends AbstractDockerContainersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * Overrides AbstractDockerContainersService::create() to dispatch the actual
     * agent-side container creation (Actions\DockerContainers\Create) right after
     * the row itself is inserted with status "creating" - mirrors how
     * GatewaysService::provisionForNetwork() dispatches its own follow-up jobs
     * immediately after creating its row.
     */
    public static function create(array $data): DockerContainers
    {
        $data['status'] = 'creating';

        $model = parent::create($data);

        dispatch(new Create($model));

        return $model;
    }
}
