<?php

namespace CybexGmbh\LaravelMultiFactor\Enums;

enum MultiFactorAuthMode: string
{
    case FORCE = 'force';
    case REQUIRED = 'required';
    case OPTIONAL = 'optional';

    public static function fromConfig(): self
    {
        return self::from(config('multi-factor.mode'));
    }
}
