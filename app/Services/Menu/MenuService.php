<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Repositories\Menu\MenuRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class MenuService implements MenuServiceInterface
{
    private $repository;

    public function __construct(
        MenuRepositoryInterface  $menuRepository
    )
    {
        $this->repository = $menuRepository;
    }
    public function getMenuItems()
    {
        return $this->determinateGates(
            $this->repository->getMenuItems()
        );
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $menuItems
     *
     * @return \Illuminate\Support\Collection
     */
    private function determinateGates($menuItems)
    {
        return $menuItems->map(function ($item) {
            if ($item->children->count()) {
                $item->children = $this->determinateGates($item->children);
            }

            if ($item->is_su && !Auth::user()->is_su) {
                return null;
            }

            if (is_null($item->gates)) {
                return $item;
            }

            if (!Gate::any($item->gates)) {
                return null;
            }

            return $item;
        })->filter(function ($item) {
            return $item !== null;
        });
    }

}