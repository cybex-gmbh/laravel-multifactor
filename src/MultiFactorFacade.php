<?php

namespace Cybex\LaravelMultiFactor;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cybex\LaravelMultiFactor\Skeleton\SkeletonClass
 */
class MultiFactorFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-multi-factor';
    }
}
