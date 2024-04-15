<?php

namespace Exceptions;

class MustHaveNIN extends \Exception
{
    public function __construct($message = 'Must have NIN', $code = 0, \Exception $previous = null)
    {
        $message = 'You must have your national identification number validated to proceed. ' .
            'Please first validate your national number, and then you can move forward with this service.';

        parent::__construct($message, $code, $previous);
    }
}
