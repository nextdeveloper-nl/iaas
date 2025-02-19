<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class NetworkNotInPoolException extends AbstractCommonsException
{
    protected $defaultMessage = 'Network not in pool';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
