<?php

namespace NextDeveloper\IAAS\EventHandlers\DatacentersUpdatedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use NextDeveloper\Commons\Common\Cache\CacheHelper;

/**
 * Class DatacentersUpdatedEvent
 *
 * @package PlusClouds\Account\Handlers\Events
 */
class DatacentersUpdatedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
