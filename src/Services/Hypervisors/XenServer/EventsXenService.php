<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

class EventsXenService
{
    public static function store($event) : bool
    {
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
        }

        return true;
    }

    public static function vmStart($event) {
        dd('vm_start');
    }
}
