<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception khi user không đủ credits
 */
class InsufficientCreditsException extends Exception
{
    protected $message = 'Insufficient credits to perform this action';
    protected $code = 422;
}
