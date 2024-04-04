<?php

namespace App\Providers;

use App\Services\Menu\MenuItemFavorite;
use App\Services\Menu\MenuItemFavoriteInterface;
use App\Services\Menu\MenuService;
use App\Services\Menu\MenuServiceInterface;
use App\Services\Notification\NotificationService;
use App\Services\Notification\NotificationServiceInterface;
use App\Services\Telegram\TelegramService;
use App\Services\Telegram\TelegramServiceInterface;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            MenuServiceInterface::class,
            MenuService::class
        );

        $this->app->bind(
            MenuItemFavoriteInterface::class,
            MenuItemFavorite::class
        );

        $this->app->bind(
            NotificationServiceInterface::class,
            NotificationService::class
        );

        $this->app->bind(
            TelegramServiceInterface::class,
            TelegramService::class
        );
    }

}
