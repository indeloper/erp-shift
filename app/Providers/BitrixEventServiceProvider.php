<?php

namespace App\Providers;

use App\Events\Bitrix\Company\CompanyUpdateEvent;
use App\Events\Bitrix\Requisite\RequisiteUpdateEvent;
use App\Events\Bitrix\Task\TaskUpdateEvent;
use App\Listeners\Bitrix\Company\CompanyUpdateListener;
use App\Listeners\Bitrix\Requisite\RequisiteUpdateListener;
use App\Listeners\Bitrix\Task\TaskUpdateListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class BitrixEventServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(
            TaskUpdateEvent::class,
            TaskUpdateListener::class,
        );

        Event::listen(
            CompanyUpdateEvent::class,
            CompanyUpdateListener::class,
        );

        Event::listen(
            RequisiteUpdateEvent::class,
            RequisiteUpdateListener::class,
        );
    }

}
