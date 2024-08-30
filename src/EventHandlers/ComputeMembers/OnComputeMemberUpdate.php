<?php

namespace NextDeveloper\IAAS\EventHandlers\ComputeMembers;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class IaasVirtualMachinesCreatedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class OnComputeMemberUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function handle($event)
    {
//        $event->model;
        /**
         * You will take statistics information from compute member object and save it to
         * compute member stats
         */
    }
}
