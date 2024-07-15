<?php

namespace App\Traits;

use App\Scopes\DefaultSortOrderScope;

trait DefaultSortable
{
    public static function bootDefaultSortable()
    {
        static::addGlobalScope(new DefaultSortOrderScope);
    }
}
