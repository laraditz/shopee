<?php

namespace Laraditz\Shopee;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Shopee\Skeleton\SkeletonClass
 */
class ShopeeFacade extends Facade
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
