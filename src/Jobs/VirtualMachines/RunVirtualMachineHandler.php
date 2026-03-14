<?php

namespace NextDeveloper\IAAS\Jobs\VirtualMachines;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Contracts\VirtualMachineHandlerInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Helpers\UserHelper;

class RunVirtualMachineHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private VirtualMachines $vm;
    private string $handlerClass;

    public function __construct(VirtualMachines $vm, string $handlerClass)
    {
        $this->vm           = $vm;
        $this->handlerClass = $handlerClass;
        $this->queue        = 'iaas-handlers';
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        try {
            /** @var VirtualMachineHandlerInterface $handler */
            $handler = app($this->handlerClass);
            $handler->handle($this->vm);
        } catch (\Throwable $e) {
            Log::error(
                '[RunVirtualMachineHandler] Handler failed asynchronously.',
                [
                    'handler' => $this->handlerClass,
                    'vm'      => $this->vm->uuid,
                    'error'   => $e->getMessage(),
                ]
            );
        }
    }
}