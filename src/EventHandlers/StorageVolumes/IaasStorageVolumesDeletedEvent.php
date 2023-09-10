<?php

namespace NextDeveloper\IAAS\EventHandlers\StorageVolumes;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasStorageVolumesDeletedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasStorageVolumesDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}