<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class UnknownServiceRoleException extends AbstractCommonsException
{
    protected $defaultMessage = 'Requested service role does not exist or is not active.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
