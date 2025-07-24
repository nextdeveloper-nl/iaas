<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Events\VirtualMachineEvents;

class EventsXenService
{
    public static function store($event) : bool
    {
        if(!(
            Str::contains($event, 'VM_STARTED') ||
            Str::contains($event, 'VM_STOPPED')
        )) {
            return false;
        }

        $charPos = strpos($event, '"');
        $charPosEnd = strpos($event, '"', $charPos+1);

        $message = substr($event, $charPos, $charPosEnd - $charPos + 1);

        $event = str_replace($message, '\'\'', $event);
        $event = str_replace('\'', '"', $event);

        $charPos = strpos($event, '<DateTime');
        $charPosEnd = strpos($event, '>', $charPos+1);

        $message = substr($event, $charPos, $charPosEnd - $charPos + 1);

        $event = str_replace($message, '""', $event);

        $event = json_decode($event, true);

        switch ($event['snapshot']['name']) {
            case 'VM_STARTED':
                self::vmStart($event);
                break;
            default:
                dd($event);
        }

        return true;
    }

    public static function vmStart($event) {
        $vm = VirtualMachines::withoutGlobalScopes()
            ->where('hypervisor_uuid', $event['snapshot']['obj_uuid'])
            ->first();

        VirtualMachineEvents::started($vm);
    }

    public static function vmStop($event)
    {
        $vm = VirtualMachines::withoutGlobalScopes()
            ->where('hypervisor_uuid', $event['snapshot']['obj_uuid'])
            ->first();

        VirtualMachineEvents::stopped($vm);
    }
}
