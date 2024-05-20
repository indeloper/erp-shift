<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $route = null;

        if ($this->route_name) {
            $route = route($this->route_name);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'route' => $route,
            'icon_path' => $this->icon_path,
            'actives' => $this->actives,
            'toggle_favorite_route' => route('layout::menu::favorite::toggle', [
                'menu_item' => $this->id,
            ]),
            'is_favorite' => auth()->user()->menuItems->contains($this->id),
        ];
    }
}
