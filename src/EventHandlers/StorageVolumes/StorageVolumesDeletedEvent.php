<?php

namespace NextDeveloper\IAAS\EventHandlers\StorageVolumesDeletedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class StorageVolumesDeletedEvent
 *
 * @package PlusClouds\Account\Handlers\Events
 */
class StorageVolumesDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}