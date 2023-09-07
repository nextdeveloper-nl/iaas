<?php

namespace NextDeveloper\IAAS\EventHandlers\IaasCloudNode;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasCloudNodeDeletedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasCloudNodeDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}