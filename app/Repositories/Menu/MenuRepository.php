<?php

declare(strict_types=1);

namespace App\Repositories\Menu;

use App\Models\Menu\MenuItem;

final class MenuRepository implements MenuRepositoryInterface
{

    public function getMenuItems()
    {
        return MenuItem::query()
            ->with(['children'])
            ->whereNull('parent_id')
            ->active()
            ->get();
    }

}