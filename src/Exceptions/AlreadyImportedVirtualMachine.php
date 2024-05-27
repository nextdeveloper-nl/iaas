<?php

namespace NextDeveloper\IAAS\Exceptions;

use NextDeveloper\Commons\Exceptions\AbstractCommonsException;

class AlreadyImportedVirtualMachine extends AbstractCommonsException
{
    protected $defaultMessage = 'This machine seems to be already imported in database. If you think that this' .
        ' is a mistake, please contact support.';

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
