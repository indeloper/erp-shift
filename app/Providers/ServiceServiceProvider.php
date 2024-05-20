<?php

namespace App\Providers;

use App\Services\Bitrix\BitrixService;
use App\Services\Bitrix\BitrixServiceInterface;
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

    public $bindings
        = [
            NotificationItemServiceInterface::class => NotificationItemService::class,
            MenuServiceInterface::class             => MenuService::class,
            MenuItemFavoriteInterface::class        => MenuItemFavorite::class,
            UserServiceInterface::class             => UserService::class,
            NotificationServiceInterface::class     => NotificationService::class,
            TelegramServiceInterface::class         => TelegramService::class,
            BitrixServiceInterface::class           => BitrixService::class,
        ];

}
