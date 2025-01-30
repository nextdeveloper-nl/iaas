<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class CannotCreateVirtualMachine extends AbstractCommonsException
{
    protected $defaultMessage = 'Cannot create virtual machine.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
