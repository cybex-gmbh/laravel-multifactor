<?php

namespace CybexGmbh\LaravelMultiFactor;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CybexGmbh\LaravelMultiFactor\Skeleton\SkeletonClass
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
