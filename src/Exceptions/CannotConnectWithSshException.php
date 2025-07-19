<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class CannotConnectWithSshException extends AbstractCommonsException
{
    protected $defaultMessage = 'We cannot connect with the server using SSH.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
