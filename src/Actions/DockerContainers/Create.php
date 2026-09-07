<?php

namespace NextDeveloper\IAAS\Actions\DockerContainers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Services\DockerContainersService;

/**
 * Creates the container on its host VM's Docker Engine via the agent.
 *
 * Runs after the DockerContainers row itself already exists (metadata only,
 * status "creating") - see DockerContainersService::create(), which dispatches
 * this action right after the row is inserted. The 'client_ref' param carries
 * this row's own uuid purely so the backend can correlate the eventual async
 * result back to this row (see HandleVmAgentEventJob::onCommandResult()) - the
 * agent itself ignores it.
 */
class Create extends AbstractAction
{
    public const EVENTS = [
        'creating:NextDeveloper\IAAS\DockerContainers',
        'created:NextDeveloper\IAAS\DockerContainers',
        'create-failed:NextDeveloper\IAAS\DockerContainers',
    ];

    public function __construct(DockerContainers $container, $params = null, $previous = null)
    {
        $this->model = $container;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Creating docker container.');

        $vm = $this->model->virtualMachine;

        if (!$vm) {
            $this->fail('This container has no host virtual machine - cannot continue.');
            return;
        }

        Events::fire('creating:NextDeveloper\IAAS\DockerContainers', $this->model);

        $params = [
            'client_ref'      => $this->model->uuid,
            'container_name'  => $this->model->name,
            'image'           => $this->model->image,
            'args'            => $this->model->command ?? [],
            'env'             => $this->model->env_vars ?? [],
            'ports'           => $this->model->ports ?? [],
            'restart_policy'  => $this->model->restart_policy,
            'cpu_limit'       => $this->model->cpu_limit,
            'memory_limit_mb' => $this->model->memory_limit_mb,
        ];

        try {
            $vm->sendAgentCommand('docker.create', $params, 120);
        } catch (\InvalidArgumentException $e) {
            $this->abortWithError($e->getMessage());
            return;
        }

        // The actual result (container_id, running state) arrives asynchronously
        // via HandleVmAgentEventJob::onCommandResult(), which resolves this row by
        // 'client_ref' and finalizes status/container_id there.
        $this->setProgress(100, 'Docker create command sent to agent.');
    }

    // Named to avoid colliding with InteractsWithQueue::fail(), which
    // AbstractAction already inherits (marks the underlying queue job itself
    // as failed - a different concern from "this container failed to create").
    private function abortWithError(string $message): void
    {
        DockerContainersService::update($this->model->uuid, [
            'status'      => 'error',
            'agent_error' => $message,
        ]);

        Events::fire('create-failed:NextDeveloper\IAAS\DockerContainers', $this->model);

        $this->setFinishedWithError('Cannot create this container: ' . $message);
    }
}
