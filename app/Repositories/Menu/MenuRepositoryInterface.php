<?php

declare(strict_types=1);

namespace App\Repositories\Menu;

interface MenuRepositoryInterface
{

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMenuItems();

    /**
     * @param $menuItemId
     *
     * @return \App\Models\Menu\MenuItem|null
     */
    public function getMenuItemById($menuItemId);

}