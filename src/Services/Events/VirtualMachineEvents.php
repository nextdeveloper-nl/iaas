<?php

namespace NextDeveloper\IAAS\Services\Events;

use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

class VirtualMachineEvents
{
    public static function started(VirtualMachines $vm) : void
    {
        if($vm->status != 'running') {
            //  We need to run health check
            dispatch(new HealthCheck($vm));
        }
    }

    public static function stopped(VirtualMachines $vm) : void
    {
        if($vm->status != 'stopped') {
            dispatch(new HealthCheck($vm));
        }
    }

    public static function paused(VirtualMachines $vm) : void
    {
        if($vm->status != 'paused') {
            dispatch(new HealthCheck($vm));
        }
    }
}
