<?php

namespace NextDeveloper\IAAS\Services\Events;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

class VirtualMachineEvents
{
    public static function started(VirtualMachines $vm) : void
    {
        if($vm->status != 'running') {
            //  Real signal that the VM didn't reach the expected state after a start
            //  event. HealthCheck (which used to investigate this automatically) has
            //  been retired - this is a hook point for a future replacement.
        }
    }

    public static function stopped(VirtualMachines $vm) : void
    {
        if($vm->status != 'stopped') {
            //  See started() above.
        }
    }

    public static function paused(VirtualMachines $vm) : void
    {
        if($vm->status != 'paused') {
            //  See started() above.
        }
    }
}
