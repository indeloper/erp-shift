<?php

declare(strict_types=1);

namespace App\Facades;

use App\Helpers\TGUserWebAppHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin TGUserWebAppHelper
 */
final class TGUserWebApp extends Facade
{

    const QUEUE_NAME = 'user';

    const REQUEST_KEY = 'tgWebAppData';

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return TGUserWebAppHelper::class;
    }

}