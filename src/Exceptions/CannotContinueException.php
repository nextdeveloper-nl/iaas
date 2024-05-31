<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class CannotContinueException extends AbstractCommonsException
{
    protected $defaultMessage = 'We cannot complete the action you want to run for the related VM.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
