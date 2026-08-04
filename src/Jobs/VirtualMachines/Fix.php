<?php

namespace NextDeveloper\IAAS\Jobs\VirtualMachines;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Contracts\BackupCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

class Fix implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $vm;

    public function __construct(VirtualMachines $vm)
    {
        $this->vm = $vm;
    }

    public function handle()
    {
        $driver = app(VirtualMachineManager::class)->getAdapter($this->vm);

        if ($driver instanceof BackupCapableInterface) {
            $driver->fixVmName($this->vm);
        } else {
            VirtualMachinesXenService::fixName($this->vm);
        }
    }
}
