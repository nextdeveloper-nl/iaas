<?php

namespace NextDeveloper\IAAS\Exceptions;

class SynchronizationException extends \Exception
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        $message = 'Cannot make the synchronization properly: ' . $message;

        parent::__construct($message, $code, $previous);
    }
}
