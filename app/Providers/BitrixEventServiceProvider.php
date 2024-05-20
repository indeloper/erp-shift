<?php

namespace App\Providers;

use App\Events\Bitrix\Task\TaskUpdateEvent;
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
    }

}
