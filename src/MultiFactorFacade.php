<?php

namespace Cybex\LaravelMultiFactor;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cybex\LaravelMultiFactor\Skeleton\SkeletonClass
 */
class MultiFactorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-multi-factor';
    }
}
