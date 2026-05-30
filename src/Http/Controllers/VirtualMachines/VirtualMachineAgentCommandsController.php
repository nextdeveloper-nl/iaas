<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachines;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VmAgentCommandService;

/**
 * Exposes VM agent commands over HTTP.
 *
 * GET  /{vm}/agent/operations        — list operations available for this VM
 * POST /{vm}/agent/{operation}       — dispatch a command, returns 202 with command UUID
 */
class VirtualMachineAgentCommandsController extends AbstractController
{
    /**
     * Return the list of operations the VM agent currently supports.
     */
    public function index($vmId)
    {
        $vm = VirtualMachinesService::getByRef($vmId);

        return $this->withArray([
            'vm_uuid'    => $vm->uuid,
            'operations' => VmAgentCommandService::getAvailableOperations($vm),
        ]);
    }

    /**
     * Dispatch a command to the VM agent asynchronously.
     * Returns 202 immediately with a command UUID the caller can poll via
     * GET /events/agent-commands/{command_uuid}.
     */
    public function dispatch(Request $request, $vmId, $operation)
    {
        $vm     = VirtualMachinesService::getByRef($vmId);
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
}
