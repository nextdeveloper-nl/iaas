<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class NotEnoughResourcesException extends AbstractCommonsException
{
    protected $defaultMessage = 'We dont have enough resources to proceed with this action. ' .
        'Please try again later or consult to your cloud provider.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
