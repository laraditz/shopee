<?php

namespace Laraditz\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Shopee\Skeleton\SkeletonClass
 */
class Shopee extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shopee';
    }
}
