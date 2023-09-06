<?php

namespace NextDeveloper\IAAS\EventHandlers\IaasComputePool;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasComputePoolCreatedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasComputePoolCreatedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}