<?php

namespace CybexGmbh\LaravelTwoFactor\Enums;

enum TwoFactorAuthMode: string
{
    case FORCE = 'force';
    case REQUIRED = 'required';
    case OPTIONAL = 'optional';

    public static function fromConfig(): self
    {
        return self::from(config('two-factor.mode'));
    }
}
