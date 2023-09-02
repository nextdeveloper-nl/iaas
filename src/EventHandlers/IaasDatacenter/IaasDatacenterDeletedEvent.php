<?php

namespace NextDeveloper\IAAS\EventHandlers\IaasDatacenter;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasDatacenterDeletedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasDatacenterDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}