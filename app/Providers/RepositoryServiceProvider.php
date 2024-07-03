<?php

namespace App\Providers;

use App\Repositories\ExceptionNotificationUser\ExceptionNotificationUserRepository;
use App\Repositories\ExceptionNotificationUser\ExceptionNotificationUserRepositoryInterface;
use App\Repositories\Menu\MenuRepository;
use App\Repositories\Menu\MenuRepositoryInterface;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Repositories\NotificationItem\NotificationItemRepository;
use App\Repositories\NotificationItem\NotificationItemRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            NotificationItemRepositoryInterface::class,
            NotificationItemRepository::class
        );

        $this->app->bind(
            ExceptionNotificationUserRepositoryInterface::class,
            ExceptionNotificationUserRepository::class
        );

        $this->app->bind(
            MenuRepositoryInterface::class,
            MenuRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );
    }
}
