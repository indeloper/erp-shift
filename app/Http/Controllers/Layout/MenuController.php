<?php

namespace App\Http\Controllers\Layout;

use App\Http\Resources\Menu\MenuResource;
use App\Services\Menu\MenuServiceInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{

    /** @var MenuServiceInterface $menuServiceInterface */
    private $service;

    /**
     * @param  MenuServiceInterface  $menuService
     */
    public function __construct(
        MenuServiceInterface $menuService
    )
    {
        $this->service = $menuService;
    }

    public function index()
    {
        return MenuResource::collection(
            $this->service->getMenuItems()
        );
    }
}