<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Repositories\Menu\MenuRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;

final class MenuItemFavorite implements MenuItemFavoriteInterface
{
    public $userRepository;

    public $menuRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        MenuRepositoryInterface $menuRepository
    ) {
        $this->userRepository = $userRepository;
        $this->menuRepository = $menuRepository;
    }

    public function toggle($menuItemId, $userId)
    {
        $user = $this->userRepository->getUserById(
            $userId
        );

        if ($user === null) {
            abort(404);
        }

        $menuItem = $this->menuRepository->getMenuItemById(
            $menuItemId
        );

        if ($menuItem === null) {
            abort(404);
        }

        $menuItem->users()->toggle([$user->id]);

        return $menuItem;
    }

    public function getFavorites($id)
    {
        $user = $this->userRepository->getUserById($id);

        return $user->menuItems;
    }
}
