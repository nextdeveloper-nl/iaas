<?php

namespace NextDeveloper\IAAS\EventHandlers\ComputeMembers;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class IaasVirtualMachinesCreatedEvent
 * @package PlusClouds\Account\Handlers\Events
 */
class OnComputeMemberCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle($event)
    {
//        $event->model;
        /**
         * You will take statistics information from compute member object and save it to
         * compute member stats
         */
    }
}
