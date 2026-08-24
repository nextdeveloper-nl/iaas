<?php

namespace NextDeveloper\IAAS\Helpers;

use NextDeveloper\Commons\Elasticsearch\Jobs\SyncModelToElasticsearchJob;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Jobs\VirtualMachines\CommentVMActionEvent;

class BindIAASEventHelper
{
    /**
     * Register all comment-producing VM lifecycle events using the single
     * authoritative source of event keys defined in CommentVMActionEvent.
     */
    public static function registerCommentAction(): void
    {
        self::registerEvents(CommentVMActionEvent::getSupportedEvents(), CommentVMActionEvent::class);
    }

    /**
     * Registers SyncModelToElasticsearchJob against VirtualMachines' lifecycle events -
     * the existing Observer already fires these via Events::fire(), so this is the only
     * wiring needed to keep the ES index in sync, no Observer changes required. Called
     * from IAASServiceProvider::boot(), guarded by config('elasticsearch.enabled').
     *
     * Event names below are hardcoded to match exactly what VirtualMachinesObserver
     * fires (NextDeveloper\IAAS\VirtualMachines, with "Database\Models" stripped) -
     * NOT \NextDeveloper\IAAS\Database\Models\VirtualMachines::class, which resolves
     * to a different string and would silently never match.
     */
    public static function registerElasticsearchSync(): void
    {
        self::registerEvents([
            'created:NextDeveloper\IAAS\VirtualMachines',
            'updated:NextDeveloper\IAAS\VirtualMachines',
            'deleted:NextDeveloper\IAAS\VirtualMachines',
            'restored:NextDeveloper\IAAS\VirtualMachines',
        ], SyncModelToElasticsearchJob::class);
    }

    /**
     * Helper to bind a list of events to an action handler class.
     *
     * @param array $events  Fully-qualified event names.
     * @param string $handlerClass Invokable action class name.
     */
    private static function registerEvents(array $events, string $handlerClass): void
    {
        foreach ($events as $event) {
            Events::listen($event, $handlerClass);
        }
    }
}
