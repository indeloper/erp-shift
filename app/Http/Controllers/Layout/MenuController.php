<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use App\Http\Resources\Menu\MenuResource;
use App\Services\Menu\MenuServiceInterface;

class MenuController extends Controller
{
    /** @var MenuServiceInterface */
    private $service;

    public function __construct(
        MenuServiceInterface $menuService
    ) {
        $this->service = $menuService;
    }

    public function index()
    {
        return MenuResource::collection(
            $this->service->getMenuItems()
        );
    }
}
