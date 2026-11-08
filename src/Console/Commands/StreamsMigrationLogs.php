<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Prints the migration services' log lines to the console while a migration command runs, so the
 * operator sees every step as it happens instead of tailing laravel.log.
 *
 *   php artisan leo:migrate-local-vm --step=run --migration-id=<uuid>      info / warning / error lines
 *   php artisan leo:migrate-local-vm --step=run --migration-id=<uuid> -v   also every command sent to the hosts
 *                                                                          and its output (debug lines)
 *
 * Only lines written by MigrationService / LocalDiskMigrationService are shown (both put their class
 * name in the message). The lines still go to the normal log as well.
 */
trait StreamsMigrationLogs
{
    protected function streamMigrationLogs(): void
    {
        $verbose = $this->output->isVerbose();

        Event::listen(MessageLogged::class, function (MessageLogged $event) use ($verbose) {
            if (!str_contains($event->message, 'MigrationService')) {
                return;
            }

            $level = $event->level;

            // debug = commands and their raw output; only with -v
            if ($level === 'debug' && !$verbose) {
                return;
            }

            $color = match ($level) {
                'error', 'critical', 'alert', 'emergency' => 'red',
                'warning'                                  => 'yellow',
                'debug'                                    => 'gray',
                default                                    => 'default',
            };

            // Shorten "NextDeveloper\IAAS\Services\Hypervisors\XenServer\LocalDiskMigrationService::copyVhdFiles | text"
            $message = preg_replace('/^(?:[A-Za-z]+\\\\)+(\w+)::/', '$1::', $event->message);

            $this->line(sprintf('<fg=gray>[%s]</> <fg=%s>%s</>', now()->format('H:i:s'), $color, OutputFormatter::escape($message)));
        });
    }
}
