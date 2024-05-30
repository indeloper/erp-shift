<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use App\Http\Resources\Menu\FavoriteMenuResource;
use App\Http\Resources\Menu\MenuResource;
use App\Services\Menu\MenuItemFavoriteInterface;

class MenuFavoriteController extends Controller
{
    public $service;

    public function __construct(
        MenuItemFavoriteInterface $service
    ) {
        $this->service = $service;
    }

    public function toggle($menuItem)
    {
        return MenuResource::make(
            $this->service->toggle(
                $menuItem,
                auth()->user()->id
            )
        );
    }

    public function favorites()
    {
        return FavoriteMenuResource::collection(
            $this->service->getFavorites(
                auth()->user()->id
            )
        );
    }
}
