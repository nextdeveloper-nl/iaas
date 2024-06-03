<?php

namespace NextDeveloper\IAAS\Jobs\VirtualMachines;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
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
        VirtualMachinesXenService::fixName($this->vm);
    }
}
