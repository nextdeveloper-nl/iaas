<?php

namespace NextDeveloper\IAAS\EventHandlers\StoragePoolsCreatedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class StoragePoolsCreatedEvent
 *
 * @package PlusClouds\Account\Handlers\Events
 */
class StoragePoolsCreatedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}