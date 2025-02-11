<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class CannotFindAvailableResourceException extends AbstractCommonsException
{
    protected $defaultMessage = 'We cannot find available resource for the related object.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
