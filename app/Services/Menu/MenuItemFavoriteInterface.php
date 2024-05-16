<?php

declare(strict_types=1);

namespace App\Services\Menu;

interface MenuItemFavoriteInterface
{
    public function toggle($menuItemId, $userId);

    public function getFavorites($id);
}
