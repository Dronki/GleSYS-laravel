<?php

namespace Dronki\GleSYS;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dronki\GleSYS\Skeleton\SkeletonClass
 */
class GleSYSFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'glesys';
    }
}
