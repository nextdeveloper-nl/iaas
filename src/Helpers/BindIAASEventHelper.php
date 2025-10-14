<?php

namespace NextDeveloper\IAAS\Helpers;

use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Jobs\VirtualMachines\CommentVMActionEvent;
use NextDeveloper\IAAS\Actions\VirtualMachines\StateChangeNotification;

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
     * Register state change notification events (subset focused on user-visible state transitions).
     */
    public static function registerStateChangeNotificationAction(): void
    {
        self::registerEvents(StateChangeNotification::getSupportedEvents(), StateChangeNotification::class);
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
