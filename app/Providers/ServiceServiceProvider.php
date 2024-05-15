<?php

namespace App\Providers;

use App\Services\Menu\MenuItemFavorite;
use App\Services\Menu\MenuItemFavoriteInterface;
use App\Services\Menu\MenuService;
use App\Services\Menu\MenuServiceInterface;
use App\Services\Notification\NotificationService;
use App\Services\Notification\NotificationServiceInterface;
use App\Services\NotificationItem\NotificationItemService;
use App\Services\NotificationItem\NotificationItemServiceInterface;
use App\Services\Telegram\TelegramService;
use App\Services\Telegram\TelegramServiceInterface;
use App\Services\User\UserService;
use App\Services\User\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            NotificationItemServiceInterface::class,
            NotificationItemService::class
        );

        $this->app->bind(
            MenuServiceInterface::class,
            MenuService::class
        );

        $this->app->bind(
            MenuItemFavoriteInterface::class,
            MenuItemFavorite::class
        );

        $this->app->bind(
            UserServiceInterface::class,
            UserService::class
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
