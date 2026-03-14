<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Contracts\VirtualMachineHandlerInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\VirtualMachines\RunVirtualMachineHandler;

/**
 * Dispatches registered handlers for a given VM lifecycle or CRUD hook.
 *
 * Supported hooks
 * ---------------
 * Lifecycle : booting, booted, shutting_down, shutdown, suspended, resumed,
 *             deploying, deployed
 * CRUD      : creating, created, updating, updated, deleting, deleted
 *
 * Registration (config/iaas.php)
 * --------------------------------
 * 'vm_hooks' => [
 *     'booting' => [
 *         \App\Handlers\VirtualMachines\VncTokenGenerator::class,
 *     ],
 *     'created' => [
 *         \App\Handlers\VirtualMachines\SetupFirewallHandler::class,
 *     ],
 * ],
 *
 * Handler contract
 * ----------------
 * Each handler must implement VirtualMachineHandlerInterface:
 *   - handle(VirtualMachines $vm): void
 *   - isAsync(): bool   →  true = queued job, false = synchronous
 */
class VirtualMachineHookDispatcher
{
    /**
     * Valid hook names. Anything outside this list is silently ignored.
     */
    private const VALID_HOOKS = [
        // Lifecycle
        'booting', 'booted',
        'shutting_down', 'shutdown',
        'suspended', 'resumed',
        'deploying', 'deployed',
        // CRUD
        'creating', 'created',
        'updating', 'updated',
        'deleting', 'deleted',
    ];

    /**
     * Dispatch all handlers registered for the given hook.
     *
     * @param  string          $hook  One of the supported hook names
     * @param  VirtualMachines $vm
     */
    public static function dispatch(string $hook, VirtualMachines $vm): void
    {
        if (!in_array($hook, self::VALID_HOOKS, true)) {
            Log::warning('[VirtualMachineHookDispatcher] Unknown hook attempted.', [
                'hook' => $hook,
                'vm'   => $vm->uuid,
            ]);

            return;
        }

        $handlers = config('iaas.vm_hooks.' . $hook, []);

        if (empty($handlers)) {
            return;
        }

        foreach ($handlers as $handlerClass) {
            self::run($handlerClass, $vm, $hook);
        }
    }

    private static function run(string $handlerClass, VirtualMachines $vm, string $hook): void
    {
        if (!class_exists($handlerClass)) {
            Log::error('[VirtualMachineHookDispatcher] Handler class not found.', [
                'handler' => $handlerClass,
                'hook'    => $hook,
                'vm'      => $vm->uuid,
            ]);

            return;
        }

        if (!is_a($handlerClass, VirtualMachineHandlerInterface::class, true)) {
            Log::error('[VirtualMachineHookDispatcher] Handler does not implement VirtualMachineHandlerInterface.', [
                'handler' => $handlerClass,
                'hook'    => $hook,
                'vm'      => $vm->uuid,
            ]);

            return;
        }

        try {
            /** @var VirtualMachineHandlerInterface $handler */
            $handler = app($handlerClass);

            if ($handler->isAsync()) {
                RunVirtualMachineHandler::dispatch($vm, $handlerClass);
            } else {
                $handler->handle($vm);
            }
        } catch (\Throwable $e) {
            Log::error('[VirtualMachineHookDispatcher] Handler failed synchronously.', [
                'handler' => $handlerClass,
                'hook'    => $hook,
                'vm'      => $vm->uuid,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}