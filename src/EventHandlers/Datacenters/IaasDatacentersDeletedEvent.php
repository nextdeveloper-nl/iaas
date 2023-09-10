<?php

namespace NextDeveloper\IAAS\EventHandlers\Datacenters;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasDatacentersDeletedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasDatacentersDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}