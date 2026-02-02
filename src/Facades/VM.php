<?php

namespace Nextdeveloper\IAAS\Facades;

use Illuminate\Support\Facades\Facade;

class VM extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vm.manager';
    }
}
