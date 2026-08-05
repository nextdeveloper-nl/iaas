<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachines;

use Illuminate\Http\Request;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Events\Database\Filters\AgentCommandsQueryFilter;
use NextDeveloper\Events\Exceptions\AgentTimeoutException;
use NextDeveloper\Events\Services\AgentCommandService;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VmAgentCommandService;

/**
 * Exposes VM agent commands over HTTP.
 *
 * GET  /{vm}/agent/operations           — list operations available for this VM
 * GET  /{vm}/agent/commands             — list previously dispatched commands and their results
 * POST /{vm}/agent/{operation}          — run a command async, returns 202 with command UUID
 * POST /{vm}/agent/{operation}/sync     — send a command and block until the agent replies
 */
class VirtualMachineAgentCommandsController extends AbstractController
{
    /**
     * Return the list of operations the VM agent currently supports.
     */
    public function index($vmId)
    {
        $vm = VirtualMachinesService::getByRef($vmId);

        if (!$vm) {
            return $this->setStatusCode(404)->withError('Virtual machine not found.', 'ERROR-VM-NOT-FOUND');
        }

        return $this->withArray([
            'vm_uuid'    => $vm->uuid,
            'operations' => VmAgentCommandService::getAvailableOperations($vm),
        ]);
    }

    /**
     * List previously dispatched commands for this VM and their status/result.
     * Accepts the same filters as GET /events/agent-commands (status, operation,
     * date ranges, ...) already scoped down to this VM's own commands.
     */
    public function commands(AgentCommandsQueryFilter $filter, Request $request, $vmId)
    {
        $vm = VirtualMachinesService::getByRef($vmId);

        if (!$vm) {
            return $this->setStatusCode(404)->withError('Virtual machine not found.', 'ERROR-VM-NOT-FOUND');
        }

        $data = VmAgentCommandService::getCommands($vm, $filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * Run a command on the VM agent asynchronously.
     * Returns 202 immediately with a command UUID the caller can poll via
     * GET /events/agent-commands/{command_uuid}.
     */
    public function run(Request $request, $vmId, $operation)
    {
        $vm = VirtualMachinesService::getByRef($vmId);

        if (!$vm) {
            return $this->setStatusCode(404)->withError('Virtual machine not found.', 'ERROR-VM-NOT-FOUND');
        }

        $params = $request->except(['_token']);

        try {
            $commandUuid = VmAgentCommandService::dispatch($vm, $operation, $params);
        } catch (\InvalidArgumentException $e) {
            return $this->withError(
                $e->getMessage(),
                'ERROR-INVALID-OPERATION',
                ['available_operations' => VmAgentCommandService::getAvailableOperations($vm)]
            );
        }

        return $this->setStatusCode(202)->withArray([
            'command_uuid' => $commandUuid,
            'status'       => 'sent',
        ]);
    }

    /**
     * Send a command to the VM agent and block until the agent replies.
     * Always returns 200; on timeout, `timeout` is true and `result` is null.
     */
    public function send(Request $request, $vmId, $operation)
    {
        $vm = VirtualMachinesService::getByRef($vmId);

        if (!$vm) {
            return $this->setStatusCode(404)->withError('Virtual machine not found.', 'ERROR-VM-NOT-FOUND');
        }

        $params  = $request->except(['_token', 'timeout_s']);
        $timeout = (int) $request->input('timeout_s', 10);

        try {
            $result = app(AgentCommandService::class)->send(
                agentType:      'vm',
                agentUuid:      $vm->uuid,
                operation:      $operation,
                params:         $params,
                timeoutSeconds: $timeout
            );
        } catch (AgentTimeoutException $e) {
            return $this->setStatusCode(200)->withArray([
                'operation' => $operation,
                'result'    => null,
                'timeout'   => true,
                'code'      => 'ERROR-AGENT-TIMEOUT',
                'message'   => $e->getMessage(),
            ]);
        }

        return $this->withArray([
            'operation' => $operation,
            'result'    => $result,
            'timeout'   => false,
        ]);
    }
}
