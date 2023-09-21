<?php

namespace NextDeveloper\IAAS\EventHandlers\StorageVolumesCreatedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class StorageVolumesCreatedEvent
 *
 * @package PlusClouds\Account\Handlers\Events
 */
class StorageVolumesCreatedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}