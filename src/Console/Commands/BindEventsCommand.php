<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Hoa\Event\Event;
use Illuminate\Console\Command;
use NextDeveloper\Events\Services\Events;

class BindEventsCommand extends Command {
    /**
     * @var string
     */
    protected $signature = 'nextdeveloper:iaas-bind-events';

    /**
     * @var string
     */
    protected $description = 'Binds the events to the listeners for IAAS module only.';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {

    }
}
