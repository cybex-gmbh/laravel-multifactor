<?php

namespace CybexGmbh\LaravelMultiFactor\Facades;

use Illuminate\Support\Facades\Facade;

class MFA extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mfa';
    }
}
