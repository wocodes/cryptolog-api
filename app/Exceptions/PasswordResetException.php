<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PasswordResetException extends Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
