<?php

namespace Cybex\LaravelMultiFactor\Exceptions;

use Exception;
use Throwable;

/**
 * Class LoginRouteNotFoundException
 *
 * Thrown when email-only login is enabled, but the required force mode or method configuration is missing.
 */
class InvalidEmailOnlyLoginConfigurationException extends Exception
{
    public function __construct(?string $message = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?? 'Email-only login is enabled, but force mode is not active or the forced method is not set to email.', $code, $previous);
    }
}
