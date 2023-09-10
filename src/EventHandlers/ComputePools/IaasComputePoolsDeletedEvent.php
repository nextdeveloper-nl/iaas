<?php

namespace NextDeveloper\IAAS\EventHandlers\ComputePools;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasComputePoolsDeletedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasComputePoolsDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}