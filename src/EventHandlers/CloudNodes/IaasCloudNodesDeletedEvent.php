<?php

namespace NextDeveloper\IAAS\EventHandlers\CloudNodes;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasCloudNodesDeletedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class IaasCloudNodesDeletedEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {

    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}