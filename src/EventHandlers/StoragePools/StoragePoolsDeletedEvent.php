<?php

namespace NextDeveloper\IAAS\EventHandlers\StoragePoolsDeletedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class StoragePoolsDeletedEvent
 *
 * @package PlusClouds\Account\Handlers\Events
 */
class StoragePoolsDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}