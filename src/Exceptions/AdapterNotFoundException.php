<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class AdapterNotFoundException extends AbstractCommonsException
{
    protected $defaultMessage = 'We cannot find the adapter for the given platform.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
