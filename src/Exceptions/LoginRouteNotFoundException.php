<?php

namespace Cybex\LaravelMultiFactor\Exceptions;

use Exception;
use Throwable;

/**
 * Class LoginRouteNotFoundException
 *
 * Thrown when the configured application's login route cannot be found.
 */
class LoginRouteNotFoundException extends Exception
{
    public function __construct(?string $message = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?? 'The configured login route could not be found.', $code, $previous);
    }
}
