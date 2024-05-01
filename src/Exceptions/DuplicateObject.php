<?php

namespace Exceptions;

class DuplicateObject extends \Exception
{
    public function __construct($message = 'Duplicate object detected', $code = 0, \Exception $previous = null)
    {
        $message = 'You need to create a unique object. Error message is: ' . $message;

        parent::__construct($message, $code, $previous);
    }
}
