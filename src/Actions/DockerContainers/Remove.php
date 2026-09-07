<?php

namespace NextDeveloper\IAAS\Actions\DockerContainers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Services\DockerContainersService;

/**
 * Removes a container from its host VM's Docker Engine via the agent. Accepts
 * an optional 'force' param (defaults to false) to remove a running container.
 */
class Remove extends AbstractAction
{
    public const EVENTS = [
        'removing:NextDeveloper\IAAS\DockerContainers',
        'removed:NextDeveloper\IAAS\DockerContainers',
        'remove-failed:NextDeveloper\IAAS\DockerContainers',
    ];

    public const PARAMS = [
        'force' => 'boolean',
    ];

    public function __construct(DockerContainers $container, $params = null, $previous = null)
    {
        $this->model = $container;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Removing docker container.');

        $vm = $this->model->virtualMachine;

        if (!$vm) {
            $this->setFinishedWithError('This container has no host virtual machine - cannot continue.');
            return;
        }

        if (!$this->model->container_id) {
            // Never actually created on the agent - just remove our own record.
            DockerContainersService::update($this->model->uuid, ['status' => 'removed']);
            $this->setProgress(100, 'Container was never created on its host; removed locally.');
            return;
        }

        Events::fire('removing:NextDeveloper\IAAS\DockerContainers', $this->model);

        DockerContainersService::update($this->model->uuid, ['status' => 'removing']);

        $force = !empty($this->params['force']);

        try {
            $vm->sendAgentCommand('docker.remove', [
                'container_id' => $this->model->container_id,
                'force'        => $force,
            ], 30);
        } catch (\InvalidArgumentException $e) {
            DockerContainersService::update($this->model->uuid, [
                'status'      => 'error',
                'agent_error' => $e->getMessage(),
            ]);
            Events::fire('remove-failed:NextDeveloper\IAAS\DockerContainers', $this->model);
            $this->setFinishedWithError('Cannot remove this container: ' . $e->getMessage());
            return;
        }

        // Final status ("removed" or "error") is set asynchronously by
        // HandleVmAgentEventJob::onCommandResult() once the agent replies.
        $this->setProgress(100, 'Docker remove command sent to agent.');
    }
}
