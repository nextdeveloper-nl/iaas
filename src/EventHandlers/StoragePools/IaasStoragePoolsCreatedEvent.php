<?php

namespace NextDeveloper\IAAS\EventHandlers\StoragePools;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasStoragePoolsCreatedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasStoragePoolsCreatedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}