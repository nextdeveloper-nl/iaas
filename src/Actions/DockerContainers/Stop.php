<?php

namespace NextDeveloper\IAAS\Actions\DockerContainers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Services\DockerContainersService;

/**
 * Stops a running container on its host VM's Docker Engine via the agent.
 */
class Stop extends AbstractAction
{
    public const EVENTS = [
        'stopping:NextDeveloper\IAAS\DockerContainers',
        'stopped:NextDeveloper\IAAS\DockerContainers',
        'stop-failed:NextDeveloper\IAAS\DockerContainers',
    ];

    public function __construct(DockerContainers $container, $params = null, $previous = null)
    {
        $this->model = $container;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Stopping docker container.');

        if (!$this->model->container_id) {
            $this->setFinishedWithError('This container has not been created on its host yet.');
            return;
        }

        $vm = $this->model->virtualMachine;

        if (!$vm) {
            $this->setFinishedWithError('This container has no host virtual machine - cannot continue.');
            return;
        }

        Events::fire('stopping:NextDeveloper\IAAS\DockerContainers', $this->model);

        DockerContainersService::update($this->model->uuid, ['status' => 'stopping']);

        try {
            $vm->sendAgentCommand('docker.stop', ['container_id' => $this->model->container_id], 30);
        } catch (\InvalidArgumentException $e) {
            DockerContainersService::update($this->model->uuid, [
                'status'      => 'error',
                'agent_error' => $e->getMessage(),
            ]);
            Events::fire('stop-failed:NextDeveloper\IAAS\DockerContainers', $this->model);
            $this->setFinishedWithError('Cannot stop this container: ' . $e->getMessage());
            return;
        }

        // Final status ("stopped" or "error") is set asynchronously by
        // HandleVmAgentEventJob::onCommandResult() once the agent replies.
        $this->setProgress(100, 'Docker stop command sent to agent.');
    }
}
