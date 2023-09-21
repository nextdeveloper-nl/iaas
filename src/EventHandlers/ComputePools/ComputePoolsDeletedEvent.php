<?php

namespace NextDeveloper\IAAS\EventHandlers\ComputePoolsDeletedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class ComputePoolsDeletedEvent
 *
 * @package PlusClouds\Account\Handlers\Events
 */
class ComputePoolsDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}